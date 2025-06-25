Finanzas_IVA

Aplicación web desarrollada en PHP-HTM-CSS-JS , para la gestión personal de ingresos y gastos. Incluye cálculo de IVA y la posibilidad de adjuntar fotos o comprobantes (PDF/imagen) a cada registro.
¿Para qué sirve?

Esta aplicación te permite:

    Registrar y categorizar tus ingresos y gastos.

    Calcular automáticamente el importe y total con IVA.

    Adjuntar comprobantes (fotos o PDFs) a cada movimiento.

    Visualizar tus registros con paginación.

Instalación y Uso (Local o Servidor)
1. Clona el Repositorio

Abre tu terminal (Git Bash, CMD, PowerShell) y ejecuta:

git clone https://github.com/CALRobot/Finanzas_IVA.git

Mueve la carpeta Finanzas_IVA a tu directorio de proyectos web (ej. C:\wamp64\www\ si usas WAMP).
2. Configura la Base de Datos

    Crea una base de datos MySQL con el nombre: finanza_app_iva_v2

    Credenciales de usuario para la DB:

        Usuario: admin

        Contraseña: 654321

    Importa el archivo database.sql (ubicado en la raíz de este proyecto) en esta nueva base de datos.

    ¡IMPORTANTE DE SEGURIDAD! Edita el archivo config/database.php y asegúrate de que las credenciales de conexión coinciden con las de tu base de datos. Nunca subas credenciales sensibles a repositorios públicos.

3. Carpeta de Subidas

    Asegúrate de que existe una carpeta llamada uploads dentro de la raíz del proyecto (Finanzas_IVA/uploads/).

    Si no existe, créala.

    En un servidor real: Asegúrate de que el servidor web (Apache, Nginx) tiene permisos de escritura sobre esta carpeta.

4. Accede a la Aplicación

Abre tu navegador y ve a la siguiente dirección:

    Local: http://localhost/Finanzas_IVA/public/

    Servidor: http://tudominio.com/Finanzas_IVA/public/ (ajusta la ruta según tu configuración del servidor)

Uso Básico

Una vez accedas a la aplicación, podrás:

    Registrarte o Iniciar Sesión: Accede a tu panel personal.

    Gestión de Ingresos/Gastos: Añade, edita y elimina registros, adjuntando comprobantes.

    Categorías: Gestiona tus categorías de ingresos y gastos.

    Tipos de IVA: Consulta los tipos de IVA configurados.

    Reportes: Visualiza resúmenes de tus finanzas.

¡Disfruta gestionando tus finanzas!
