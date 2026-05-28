# scripts_ia/procesar_rag.py
import sys
import os
import json
from pypdf import PdfReader
from groq import Groq  

# 1. CONFIGURACIÓN: Tu API Key activa de Groq
API_KEY_GROQ = "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"

def extraer_texto_pdf(ruta_pdf):
    """Abre el PDF del manual y extrae el texto de forma segura."""
    if not ruta_pdf or not os.path.exists(ruta_pdf):
        return ""
    try:
        reader = PdfReader(ruta_pdf)
        texto = ""
        for i in range(min(len(reader.pages), 5)):
            contenido = reader.pages[i].extract_text()
            if contenido:
                texto += contenido + "\n"
        
        if len(texto) > 15000:
            texto = texto[:15000] + "\n[Texto truncado]"
        return texto
    except Exception:
        return ""

def generar_diagnostico(falla_usuario, texto_manual):
    """Analiza la entrada y decide inteligentemente si es un saludo o una falla real."""
    try:
        client = Groq(api_key=API_KEY_GROQ)
        
        # --- PROMPT BLINDADO CONTRA INCOHERENCIAS ---
        prompt_sistema = (
            "Eres el Asistente de Mantenimiento con IA de la planta SmartMaintenance. Eres un ingeniero experto, "
            "carismático, muy amigable y con excelente sentido común venezolano.\n\n"
            
            "REGLAS CRÍTICAS DE EVALUACIÓN DE ENTRADA:\n"
            "1. SI EL USUARIO SOLO SALUDA, PREGUNTA CÓMO ESTÁS O HABLA CASUALMENTE (Ej: 'hola', 'cómo estás mi pana', 'buen día'):\n"
            "   - NO uses la información del manual técnico.\n"
            "   - NO inventes fallas ni des soluciones a problemas que el usuario no ha mencionado.\n"
            "   - Responde al saludo con buena onda, humor sano venezolano y emojis (🛠️, 😂, 💡).\n"
            "   - Pregúntale directamente en qué equipo o reporte de falla necesita que le metas mano hoy.\n"
            "   - La prioridad SIEMPRE debe ser 'Rutinaria'.\n\n"
            
            "2. SI EL USUARIO REPORTA UNA FALLA O PREGUNTA ALGO TÉCNICO REAL (Ej: 'está echando humo', 'no arranca'):\n"
            "   - Ve directo al grano. Usa el manual provisto para darle la solución técnica exacta sin rodeos.\n"
            "   - Cierra con 1 o 2 preguntas clave para profundizar en el diagnóstico.\n"
            "   - Asigna la prioridad correcta ('Rutinaria', 'Urgente' o 'Crítica').\n\n"
            
            "REGLA CRÍTICA DE SALIDA:\n"
            "Debes responder ÚNICAMENTE con un objeto JSON estructurado exactamente así, sin texto adicional fuera del objeto y sin bloques markdown:\n"
            "{\n"
            "  \"prioridad\": \"Rutinaria\" o \"Urgente\" o \"Crítica\",\n"
            "  \"diagnostico\": \"Tu respuesta aquí (usa saltos de línea \\n para separar los párrafos si es necesario)\"\n"
            "}"
        )

        prompt_final = (
            f"CONTEXTO TÉCNICO (USAR SOLO SI HAY UNA FALLA REAL):\n{texto_manual}\n\n"
            f"MENSAJE DEL OPERADOR/TÉCNICO:\n{falla_usuario}\n\n"
            f"Genera tu respuesta en el formato JSON requerido:"
        )

        completion = client.chat.completions.create(
            model="llama-3.1-8b-instant",  
            messages=[
                {"role": "system", "content": prompt_sistema},
                {"role": "user", "content": prompt_final}
            ],
            response_format={"type": "json_object"},
            temperature=0.5  # Bajamos más la temperatura para que sea ultra obediente a las reglas
        )
        
        texto_respuesta = completion.choices[0].message.content.strip()
        json.loads(texto_respuesta)
        return texto_respuesta

    except Exception as e:
        error_contingencia = {
            "prioridad": "Urgente",
            "diagnostico": f"¡Epa mi pana! Hubo un detalle en la comunicación con el servidor: [{str(e)}]. Intenta enviar el reporte de nuevo."
        }
        return json.dumps(error_contingencia, ensure_ascii=False)

if __name__ == "__main__":
    if hasattr(sys.stdout, 'reconfigure'):
        sys.stdout.reconfigure(encoding='utf-8')
        
    if len(sys.argv) < 3:
        resultado_error = {"prioridad": "Rutinaria", "diagnostico": "Error: Faltan argumentos técnicos."}
        print(json.dumps(resultado_error, ensure_ascii=False))
        sys.exit(1)
        
    falla_recibida = sys.argv[1]
    ruta_manual = sys.argv[2]
    
    texto_extraido = extraer_texto_pdf(ruta_manual)
    if not texto_extraido:
        texto_extraido = "No hay manual disponible."
        
    respuesta_json = generar_diagnostico(falla_recibida, texto_extraido)
    print(respuesta_json)
