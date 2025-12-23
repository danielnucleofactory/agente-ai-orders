Vista tendencia: 
- Cantida de PO:
Cantidad de PO	PO por etapa	Mide la cantidad de órdenes de compra (PO) y TEUs en cada etapa del proceso logístico, permitiendo visualizar la distribución del flujo operativo. Incluye además el porcentaje de volumen total que representa cada etapa, facilitando la identificación de cuellos de botella y concentración de carga en el proceso.	POs_por_Etapa = COUNT(PO_ID)	"Etapa 
# PO
% de volumen
Ruta Logistica
Cliente"	"La fecha que determina el periodo será la fecha de creación 
No se duplicarán conteos entre etapas. 
Agrupar por etapa"
Cantidad de PO	POs con retraso según Carga Lista (CL)	Indica la cantidad de órdenes de compra cuya fecha real de carga lista es posterior a la fecha de carga lista teórica comprometida. Este indicador permite evaluar el nivel de incumplimiento del proveedor o del proceso previo al embarque.	POs_Retrasadas_CL = COUNT(PO_ID) WHERE Fecha_CL_Real > Fecha_CL_Teorica	"Tabla inicial:  Cantidad de PO, Días promedio de atraso, Maximo atraso

Subtabla: Número de PO, Proveedor de mercancía, Días de atraso, Etapa"	"Se contabiliza toda PO cuya diferencia sea > 0 días.
Agrupar por Proveedor de mercancía"
Cantidad de PO	POs con adelanto según Carga Lista (CL)	Representa la cantidad de órdenes de compra cuya fecha real de carga lista se registró antes o es igual a la fecha teórica planificada. Este indicador permite identificar adelantamientos operativos que pueden impactar la planificación de transporte, consolidación o capacidad.	"POs_Adelantadas_CL = COUNT(PO_ID)
WHERE Fecha_CL_Real =< Fecha_CL_Teorica"	"Tabla inicial:   Cantidad de PO, Días promedio de adelanto, Maximo adelanto

Subtabla: Número de PO, Proveedor de mercancía, Días de adelanto, Etapa"	"Permite identificar proveedores con cumplimientos superiores o mejoras potenciales en los algoritmos de reposición.
Agrupar por Proveedor de mercancía"
Cantidad de PO	Capacidad (Allocation)	Mide la cantidad de órdenes de compra que cuentan con una fecha real de salida (ETD Real) dentro del período de análisis. Este indicador refleja el uso efectivo de la capacidad asignada y el nivel de ejecución del plan de embarques.	"Capacidad_Allocation =
COUNT(PO_ID) WHERE ETD_Real IS NOT NULL AND ETD_Real BETWEEN Fecha_Inicio AND Fecha_Fin"	"Tabla inicial: Proveedor de mercancía,  Proveedor de servicio, Cantidad de PO, % de participación, Días promedio de tránsito

Subtabla: Número de PO, Naviera, Días de transito"	"No compara contra ETA; solo contabiliza capacidad real del mes.
La agrupación es por proveedor de mercancía"
Cantidad de PO	POs en Puerto de Transbordo	Indica la cantidad de órdenes de compra que se encuentran actualmente en un puerto de transbordo. Este indicador permite monitorear cargas en tránsito intermedio y detectar posibles riesgos de congestión, demoras o reprocesos en la cadena logística.	"POs_Transbordo = COUNT(PO_ID)
WHERE Ubicacion_Actual = 'Puerto de Transbordo'"	"Tabla inicial: Puerto Transbordo, Cantidad PO 

Subtabla con:
Número de PO, Proveedor de mercancía, Proveedor de servicio, Naviera, Puerto de transbordo, N° de transbordo (2, 3 o 4 (si aplica)), Días transcurridos en cada transbordo (ETA/ETD)"	"Requiere leer información desde Port (no desde Next).
Se requiere contabilizar los dias en puerto de transbordo"
Cantidad de PO	POs con ATA (Puerto de destino)	Mide la cantidad de órdenes de compra que ya registran una fecha real de arribo (ATA Real) en el puerto de destino final. Este indicador refleja el volumen de carga efectivamente arribada y es clave para el control de tiempos de tránsito y planificación de la operación downstream.	"POs_con_ATA =COUNT(PO_ID)
WHERE ATA_Real_Destino IS NOT NULL"	"Tabla inicial: Cliente, Ruta Logística, Cantidad de PO, Proveedor de servicio

Subtabla: Número de PO, Proveedor de mercancía, Naviera, Puerto de descarga, Fecha ETA Real"	NA
Cantidad de PO	Carga lista VS ETD Real	Medir por PO el tiempo entre fecha de carga lista variable (confirmado)  vs ETD real, se requiere llevar una medicion de ese tiempo para revisar si se cumple el indicador interno de CRD vs ETD real en donde la meta es 15 dias para todos los clientes	Fecha de ETD real vs carga lista validada	"Visualizar por cliente, naviera y proveedor de servicio, puerto de carga, meta 15 días y resultado, % cumplimiento, Cantidad de PO por cliente.
"	Es de suma importancia para la organización este indicador y visualizacion, se debe poder agrupar o filtar por puerto, podriamos pensar en agrpar para este indicador y sgun el puertyo de carga una region y qe cada region cuente con una meta
Cantidad de PO	Tiempo de transito	Tiempo de transito total, ETA final vs ETD real	ATA vs ATD	Contabilizar por PO = Cantidad PO, Cliente, Ruta Logistica, # días de transito, Desviación vs meta	"Seria genial poder agrupar por grupos de tiempo en tractos de 10 dias, que se filtre por los tractos y contabilice por PO
Ademas de una vista general "

- Cantidad de TEUs:
Cantidad de Teus	Tiemteus de transito	Comparar entre dos periodos el tiempo de transito 	ATA vs ATD	Cliente, ruta logistica, naviera, proveedor de servicio, puerto de carga	Se requiere que se pueda revisar por proveedor de servicio y/o naviera asociado a un cliente y/o ruta logistica el tiempo de transito, es importante que se pueda filtar o agrupar por puerto
Cantidad de Teus	CRD VS ATD	Comparar entre dos periodos el tiempo entre carga lista validada y ATD	ATD vs Carga lista validada	Cliente, ruta logistica, naviera, proveedor de servicio, puerto de carga	Se requiere que se pueda revisar por proveedor de servicio y/o naviera asociado a un cliente y/o ruta logistica el tiempo de transito
Cantidad de TEUs	TEUs por etapa	"Mide la cantidad de contenedores estandar (TEUs) en cada etapa del proceso logístico y el porcentaje del volumen total (TEUs) que representa cada etapa. Permite ver concentración de volumen y posibles cuellos de botella por etapa. Para todos los Kpi de TEUs considerar: 
Contenedor 20"" = 1 
Contenedor 40"" = 2
LCL = 0,25"	"TEUs_por_Etapa = SUM(TEUs_PO)  agrupado por Etapa donde:
Contenedor 20"" = 1 
Contenedor 40"" = 2
LCL = 0,25
"	"Etapa 
# TEUs
% de volumen"	"La fecha que determina el periodo será la fecha de creación 
No se duplicarán conteos entre etapas. 
Agrupar por etapa"
Cantidad de TEUs	TEUs con retraso según Carga Lista (CL)	Total de TEUs asociados a POs cuya CL Real es posterior a la CL Teórica. Mide incumplimiento en volumen (no solo en número de POs).	"TEUs_Retrasados_CL =
SUM(TEUs_PO)
WHERE Fecha_CL_Real > Fecha_CL_Teorica"	"Tabla inicial:  Cantidad de TEUs, Días promedio de atraso, Maximo atraso

Subtabla: Número de TEUs, Proveedor de mercancía, Días de atraso, Etapa"	"Se contabiliza toda TEUs cuya diferencia sea > 0 días.
Agrupar por Proveedor"
Cantidad de TEUs	TEUs con adelanto según Carga Lista (CL)	Total de TEUs asociados a POs cuya CL Real es anterior a la CL Teórica. Refleja adelantamientos en volumen que pueden impactar planificación y capacidad.	"TEUs_Adelantados_CL = SUM(TEUs_PO)
WHERE Fecha_CL_Real =< Fecha_CL_Teorica"	"Tabla inicial:   Cantidad de TEUs, Días promedio de adelanto, Maximo adelanto

Subtabla: Número de TEUs, Proveedor de mercancía, Días de adelanto, Etapa"	"Permite identificar proveedores con cumplimientos superiores o mejoras potenciales en los algoritmos de reposición.
Agrupar por Proveedor"
Cantidad de TEUs	Capacidad (Allocation)	Total de TEUs de POs que cuentan con ETD Real dentro del período analizado. Refleja el volumen efectivamente ejecutado (embarcado/salido) en el período.	TEUs_Capacidad_Allocation = SUM(TEUs_PO) WHERE ETD_Real IS NOT NULL AND ETD_Real BETWEEN Fecha_Inicio AND Fecha_Fin	"Tabla inicial: Proveedor de mercancía,  Proveedor de servicio, Cantidad de TEUs, % de participación, Días promedio de tránsito

Subtabla: Número de PO, Cantidad de TEUs Naviera, Días de transito"	No compara contra ETA; solo contabiliza capacidad real del mes.
Cantidad de TEUs	TEUs en Puerto de Transbordo	Total de TEUs correspondientes a POs que se encuentran actualmente en el puerto de transbordo. Permite monitorear volumen en tránsito intermedio y riesgo por congestión/demoras.	TEUs_Transbordo = SUM(TEUs_PO) WHERE Ubicacion_Actual = 'Puerto de Transbordo'	"Tabla inicial: Puerto Transbordo, Cantidad PO 

Subtabla con:
Número de TEUs, Proveedor de mercancía, Proveedor de servicio, Naviera, Puerto de transbordo, N° de transbordo (2, 3 o 4 (si aplica)), Días transcurridos en cada transbordo (ETA/ETD)"	"Requiere leer información desde Port (no desde Next).
Se requiere contabilizar los dias en puerto de transbord"
Cantidad de TEUs	TEUs con ATA (Puerto de destino)	Total de TEUs de POs que ya registran ATA Real en el puerto final. Indica volumen efectivamente arribado al destino.	TEUs_con_ATA = SUM(TEUs_PO) WHERE ATA_Real_Destino IS NOT NULL	"Tabla inicial: Cliente, Ruta Logística, Cantidad de TEUs, Proveedor de servicio

Subtabla: Número de TEUs, Proveedor de mercancía, Naviera, Puerto de descarga, Fecha ETA Real"	NA
Cantidad de TEUs	Carga lista VS ETD Real	Medir por TEUS el tiempo entre fecha de carga lista real o carga lista validada vs ETD real, se requiere llevar una medicion de ese tiempo para revisar si se cumple el indicador interno de CRD vs ETD real en donde la meta es 15 dias para todos los clientes	Fecha de ETD real vs carga lista validada	"Visualizar por cliente, naviera y proveedor de servicio, puerto de carga, meta 15 días y resultado, % cumplimiento, Cantidad de TEUs por cliente.
"	Es de suma importancia para la organización este indicador y visualizacion, se debe poder agrupar o filtar por puerto, podriamos pensar en agrpar para este indicador y sgun el puertyo de carga una region y qe cada region cuente con una meta
Cantidad de TEUs	Tiempo de transito	Tiempo de transito total, ETA final vs ETD real	ATA vs ATD	Contabilizar por contenedor (TEUs) = Cantidad TEUs, Cliente, Ruta Logistica, # días de transito, Desviación vs meta	"Seria genial poder agrupar por grupos de tiempo en tractos de 10 dias, que se filtre por los tractos y contabilice por PO
Ademas de una vista general "

Vista Comparativo
- Cantida de po:
PO con ATD	Mide la cantidad de órdenes de compra (PO) que cuentan con ATD Real (Actual Time of Departure), este indicador permite evaluar el nivel de ejecución de salidas reales y comparar el desempeño entre dos períodos.	"PO_con_ATD_P1 = COUNT(PO_ID) WHERE ATD_Real IS NOT NULL AND ATD_Real BETWEEN Fecha_Inicio_P1 AND Fecha_Fin_P1

PO_con_ATD_P2 =COUNT(PO_ID) WHERE ATD_Real IS NOT NULL AND ATD_Real BETWEEN Fecha_Inicio_P2 AND Fecha_Fin_P2"	Proveedor de mercancía, Cantidad de PO Periodo A, Cantidad de PO Periodo A, % de variación	NA
PO con ATA	Mide la cantidad de órdenes de compra que cuentan con ATA Real (Actual Time of Arrival) en el puerto de destino dentro del período analizado. Permite comparar el volumen de arribos efectivos entre dos períodos.	"PO_con_ATA_P1 = COUNT(PO_ID) WHERE ATA_Real_Destino IS NOT NULL AND ATA_Real_Destino BETWEEN Fecha_Inicio_P1 AND Fecha_Fin_P1 Cantidad de PO con ATA – Período 2

tPO_con_ATA_P2 = COUNT(PO_ID) WHERE ATA_Real_Destino IS NOT NULL AND ATA_Real_Destino BETWEEN Fecha_Inicio_P2 AND Fecha_Fin_P2"	Proveedor de mercancía, Cantidad de PO Periodo A, Cantidad de PO Periodo A, % de variación	NA
PO con atraso CL	Mide la cantidad de órdenes de compra cuya fecha real de Carga Lista (CL Real) es posterior a la fecha de Carga Lista Teórica, dentro del período de análisis. Permite comparar el nivel de atrasos operativos entre dos períodos.	"PO_Atraso_CL_P1 = COUNT(PO_ID) WHERE Fecha_CL_Real > Fecha_CL_Teorica
AND Fecha_CL_Real BETWEEN Fecha_Inicio_P1 AND Fecha_Fin_P1
PO con atraso CL – Período 2

PO_Atraso_CL_P2 = COUNT(PO_ID) WHERE Fecha_CL_Real > Fecha_CL_Teorica
AND Fecha_CL_Real BETWEEN Fecha_Inicio_P2 AND Fecha_Fin_P2"	Proveedor de mercancía, Cantidad de PO Periodo A, Cantidad de PO Periodo A, % de variación	NA
PO con adelanto CL	Mide la cantidad de órdenes de compra cuya fecha real de Carga Lista (CL Real) es anterior a la fecha de Carga Lista Teórica, dentro del período analizado. Permite comparar el comportamiento de adelantamientos entre dos períodos.	"PO_Adelanto_CL_P1 =COUNT(PO_ID)
WHERE Fecha_CL_Real < Fecha_CL_Teorica
AND Fecha_CL_Real BETWEEN Fecha_Inicio_P1 AND Fecha_Fin_P1
PO con adelanto CL – Período 2

PO_Adelanto_CL_P2 = COUNT(PO_ID)
WHERE Fecha_CL_Real < Fecha_CL_Teorica
AND Fecha_CL_Real BETWEEN Fecha_Inicio_P2 AND Fecha_Fin_P2"	Proveedor de mercancía, Cantidad de PO Periodo A, Cantidad de PO Periodo A, % de variación	NA
Tiempo de transito	Comparar entre dos periodos el tiempo de transito 	ATA vs ATD	Cliente, ruta logistica, naviera, proveedor de servicio, puerto de carga	Se requiere que se pueda revisar por proveedor de servicio y/o naviera asociado a un cliente y/o ruta logistica el tiempo de transito, es importante que se pueda filtar o agrupar por puerto
CRD VS ATD	Comparar entre dos periodos el tiempo entre carga lista validada y ATD	ATD vs Carga lista validada	Cliente, ruta logistica, naviera, proveedor de servicio, puerto de carga	Se requiere que se pueda revisar por proveedor de servicio y/o naviera asociado a un cliente y/o ruta logistica el tiempo de transito

- Cantidad de TEUs
Tiemteus de transito	Comparar entre dos periodos el tiempo de transito 	ATA vs ATD	Cliente, ruta logistica, naviera, proveedor de servicio, puerto de carga	Se requiere que se pueda revisar por proveedor de servicio y/o naviera asociado a un cliente y/o ruta logistica el tiempo de transito, es importante que se pueda filtar o agrupar por puerto
CRD VS ATD	Comparar entre dos periodos el tiempo entre carga lista validada y ATD	ATD vs Carga lista validada	Cliente, ruta logistica, naviera, proveedor de servicio, puerto de carga	Se requiere que se pueda revisar por proveedor de servicio y/o naviera asociado a un cliente y/o ruta logistica el tiempo de transito
TEUs con ATD	Mide la cantidad de TEUs que cuentan con ATD Real (Actual Time of Departure), este indicador permite evaluar el nivel de ejecución de salidas reales y comparar el desempeño entre dos períodos.	"TEUs_con_ATD_P1 = COUNT(TEUs_ID) WHERE ATD_Real IS NOT NULL AND ATD_Real BETWEEN Fecha_Inicio_P1 AND Fecha_Fin_P1

TEUs_con_ATD_P2 =COUNT(TEUs_ID) WHERE ATD_Real IS NOT NULL AND ATD_Real BETWEEN Fecha_Inicio_P2 AND Fecha_Fin_P2"	Proveedor de mercancía, Cantidad de TEUs Periodo A, Cantidad de PO Periodo A, % de variación	NA
TEUs con ATA	Mide la cantidad de TEUs que cuentan con ATA Real (Actual Time of Arrival) en el puerto de destino dentro del período analizado. Permite comparar el volumen de arribos efectivos entre dos períodos.	"TEUs_con_ATA_P1 = COUNT(TEUs_ID) WHERE ATA_Real_Destino IS NOT NULL AND ATA_Real_Destino BETWEEN Fecha_Inicio_P1 AND Fecha_Fin_P1 Cantidad de TEUs con ATA – Período 2

TEUs_con_ATA_P2 = COUNT(TEUs_ID) WHERE ATA_Real_Destino IS NOT NULL AND ATA_Real_Destino BETWEEN Fecha_Inicio_P2 AND Fecha_Fin_P2"	Proveedor de mercancía, Cantidad de TEUs Periodo A, Cantidad de PO Periodo A, % de variación	NA
TEUs con atraso CL	Mide la cantidad de TEUs cuya fecha real de Carga Lista (CL Real) es posterior a la fecha de Carga Lista Teórica, dentro del período de análisis. Permite comparar el nivel de atrasos operativos entre dos períodos.	"TEUs_Atraso_CL_P1 = COUNT(TEUs_ID) WHERE Fecha_CL_Real > Fecha_CL_Teorica
AND Fecha_CL_Real BETWEEN Fecha_Inicio_P1 AND Fecha_Fin_P1
TEUs con atraso CL – Período 2

TEUs_Atraso_CL_P2 = COUNT(TEUs_ID) WHERE Fecha_CL_Real > Fecha_CL_Teorica
AND Fecha_CL_Real BETWEEN Fecha_Inicio_P2 AND Fecha_Fin_P2"	Proveedor de mercancía, Cantidad de TEUs Periodo A, Cantidad de PO Periodo A, % de variación	NA
TEUs con adelanto CL	Mide la cantidad de TEUs cuya fecha real de Carga Lista (CL Real) es anterior a la fecha de Carga Lista Teórica, dentro del período analizado. Permite comparar el comportamiento de adelantamientos entre dos períodos.	"TEUs_Adelanto_CL_P1 =COUNT(TEUs_ID)
WHERE Fecha_CL_Real < Fecha_CL_Teorica
AND Fecha_CL_Real BETWEEN Fecha_Inicio_P1 AND Fecha_Fin_P1
TEUs con adelanto CL – Período 2

TEUs_Adelanto_CL_P2 = COUNT(TEUs_ID)
WHERE Fecha_CL_Real < Fecha_CL_Teorica
AND Fecha_CL_Real BETWEEN Fecha_Inicio_P2 AND Fecha_Fin_P2"	Proveedor de mercancía, Cantidad de TEUs Periodo A, Cantidad de PO Periodo A, % de variación	NA

Vista PO v/s TEUs
PO vs TEUS por etapa	Este indicador muestra la cantidad de PO y el volumen asociado en TEUs para cada una de las etapas del proceso logístico. Permite analizar la distribución de órdenes y volumen a lo largo del flujo operativo, identificar concentraciones de carga por etapa y detectar posibles cuellos de botella entre planificación y ejecución.	"POs_Etapa = COUNTDISTINCT(PO_ID)  agrupado por Etapa_Logistica
TEUs_Etapa = SUM(TEUs_PO)  agrupado por Etapa_Logistica"	Etapa, Cantidad de PO,  Cantidad de TEUs	NA
PO vs TEUS por periodo	Este indicador compara la cantidad de PO y la cantidad de TEUs en cuatro períodos de análisis: semana actual, semana anterior, mes actual y mes anterior. Incluye además una variación de PIOs entre períodos comparables, lo que permite evaluar la evolución del volumen de órdenes y su impacto en capacidad, facilitando el seguimiento de tendencias de corto plazo.	"POs_X = COUNTDISTINCT(PO_ID)
WHERE Fecha_Analisis BETWEEN Inicio_X AND Fin_X TEUs en período X

TEUs_X = SUM(TEUs_PIO) WHERE Fecha_Analisis BETWEEN Inicio_X AND Fin_X"	Periodo, Etapa, Cantidad de PO,  Cantidad de TEUs	NA
PO vs TEUS por proveedor de servicio	Este indicador mide la cantidad de POs y el volumen total en TEUs asociados a cada proveedor de mercancía. Permite analizar la contribución de cada proveedor al volumen total, identificar proveedores con mayor impacto logístico y apoyar la toma de decisiones relacionadas con planificación, negociación y gestión de desempeño.	"POs_Proveedor = COUNTDISTINCT(PO_ID)  agrupado por Proveedor
TEUs_Proveedor = SUM(TEUs_PO)  agrupado por Proveedor"	Proveedor de servicio, Etapa, Cantidad de PO,  Cantidad de TEUs	NA
PO vs TEUS por naviera	Este indicador muestra la cantidad de POs  y el volumen en TEUs gestionados por cada naviera, permitiendo evaluar la participación y relevancia de cada operador marítimo. Facilita el análisis de dependencia, concentración de volumen y desempeño logístico por naviera, apoyando decisiones de asignación de capacidad y diversificación de rutas	"POs_Naviera = COUNTDISTINCT(PO_ID)  agrupado por Naviera
TEUs_Naviera = SUM(TEUs_PO)  agrupado por Naviera"	Naviera, Etapa, Cantidad de PO,  Cantidad de TEUs	NA

Vista Proyección
- Cantidad PO
Llegadas futuras	"Este indicador mide la cantidad de PO por etapas predefinidas del proceso logístico (Producción, Booking y Tránsito), distribuidas por semana calendario del año (Semana 01 a Semana 52/53), utilizando una fecha específica según la etapa. El indicador permite anticipar flujos futuros, planificar recursos y evaluar la confiabilidad de las estimaciones de arribo, mostrando la proyección semanal del volumen de órdenes en cada etapa.

El cálculo consiste en contabilizar las órdenes cuya fecha asociada a la etapa cae en una semana determinada, de acuerdo con la siguiente lógica:
Producción: se utiliza la Fecha de Carga Lista Teórica.
Booking: se utiliza la Fecha de Autorización de Booking.
Tránsito: se utiliza la Fecha de la etapa Tránsito (por ejemplo, ETA estimado)."	"POs_con_ETA_Estimado = COUNT(PO_ID)
WHERE ETA_Estimado IS NOT NULL
GROUP BY Ruta_Logistica, Semana(ETA_Estimado)

Órdenes_Etapa_Semana_N =
COUNT(PO_ID) WHERE Fecha_Etapa IS NOT NULL
AND Semana(Fecha_Etapa) = Semana_N
Donde Fecha_Etapa corresponde a:
Producción → Fecha_CL_Teorica
Booking → Fecha_Autorizacion_Booking
Tránsito → Fecha_Etapa_Transito"	Etapa, Semana 00-0000	"Considerar solo una fecha por orden y etapa para evitar doble conteo.
Notas Miguel
Para etapas produccion y booking
Produccion seria: Fecha de carga lista teorica + 15 dias + ""x"" dias de transito dependiendo del origen (region) y destno
Booking: Fecha de ETD estimado +  ""x"" dias de transito dependiendo del origen (region) y destino
Para produccion y booking deberia ser:
Asia - 65 dias
Miami - 15 dias
Colombia - 15 dias
Brasil  - 25 dias
Europa - 30 dias
Miguel debe compartir lista de país / ´´uerto"

- Cantidad TEUs
Llegadas futuras	"Este indicador mide la cantidad de PO por etapas predefinidas del proceso logístico (Producción, Booking y Tránsito), distribuidas por semana calendario del año (Semana 01 a Semana 52/53), utilizando una fecha específica según la etapa. El indicador permite anticipar flujos futuros, planificar recursos y evaluar la confiabilidad de las estimaciones de arribo, mostrando la proyección semanal del volumen de órdenes en cada etapa.

El cálculo consiste en contabilizar las órdenes cuya fecha asociada a la etapa cae en una semana determinada, de acuerdo con la siguiente lógica:
Producción: se utiliza la Fecha de Carga Lista Teórica.
Booking: se utiliza la Fecha de Autorización de Booking.
Tránsito: se utiliza la Fecha de la etapa Tránsito (por ejemplo, ETA estimado)."	"POs_con_ETA_Estimado = COUNT(PO_ID)
WHERE ETA_Estimado IS NOT NULL
GROUP BY Ruta_Logistica, Semana(ETA_Estimado)

Órdenes_Etapa_Semana_N =
COUNT(PO_ID) WHERE Fecha_Etapa IS NOT NULL
AND Semana(Fecha_Etapa) = Semana_N
Donde Fecha_Etapa corresponde a:
Producción → Fecha_CL_Teorica
Booking → Fecha_Autorizacion_Booking
Tránsito → Fecha_Etapa_Transito"	Etapa, Semana 00-0000	"Considerar solo una fecha por orden y etapa para evitar doble conteo.
Notas Miguel
Para etapas produccion y booking
Produccion seria: Fecha de carga lista teorica + 15 dias + ""x"" dias de transito dependiendo del origen (region) y destno
Booking: Fecha de ETD estimado +  ""x"" dias de transito dependiendo del origen (region) y destino

Para produccion y booking deberia ser:
Asia - 65 dias
Miami - 15 dias
Colombia - 15 dias
Brasil  - 25 dias
Europa - 30 dias"