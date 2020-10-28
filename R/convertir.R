# 
# Script transformar un csv de recibos a formato GTWIN.
# 
# Requisitos:
#  - El fichero csv debe tener codificación UTF-8 sin BOM
#  - El separador es ";".
#  - Los campos de texto deben estar entre "comillas".
#  - El fichero csv debe contener cabeceras que tener el siguiente formato:
#       Nombre;Apellido1;Apellido2;Dni;Importe;Cuenta_Corriente;Referencia_Externa;Presupuesto;Institucion;Tipo_Ingreso;Tributo;Fecha_Inicio_Pago;Fecha_Limite_Pago;Referencia_C19;Cuerpo1;Cuerpo2;Cuerpo3;Cuerpo4;Cuerpo5;Cuerpo6;Cuerpo7
#
# Los recibos pueden ser domiciliados o no domiciliados.
#
# Author: Iker Bilbao
#

########################################################################
# Configurando el entorno de trabajo
########################################################################

if (length(args)==0) {
	stop("Debe incidar el fichero a convertir: Ej. Rscript convertir_a_formato_GTWIN-berria.R origen.csv", call.=FALSE)
}

rm(list=ls())
setwd(".")
print(getwd())
args = commandArgs(trailingOnly=TRUE)

# Instalar los paquetes necesarios
packages <- c("stringr","lubridate", "dplyr")
new <- packages[!(packages %in% installed.packages()[,"Package"])]
if(length(new)) install.packages(new, repos = "http://cran.us.r-project.org", dependencies = TRUE )
a=lapply(packages, require, character.only=TRUE)

library(stringr)
library(lubridate)
library(dplyr)

source("funciones.R")

split_path <- function(x) if (dirname(x)==x) x else c(basename(x),split_path(dirname(x)))

tipo_recibo = "AU"
estado_recibo = "V"
if ( !is.na(args[2]) ) {
	tipo_recibo = args[2]
}
if ( !is.na(args[3]) ) {
	estado_recibo = args[3]
}

if (tipo_recibo == "ID" || tipo_recibo == "RB") {
	estado_recibo = "P"
}

# Leer fichero de origen
print(paste("Convertiendo fichero:", args[1])) 
print(paste(" Tipos de recibo: ", tipo_recibo))
print(paste(" Estado de recibo: ", estado_recibo))
data = read.csv(args[1],sep=";", encoding = "UTF-8", stringsAsFactors = FALSE, header = TRUE)
fichero = split_path(args[1])[1]
fichero_sin_extension = strsplit(fichero,".",TRUE)[[1]][1]
# extension = strsplit(fichero,".",TRUE)[[1]][2]
path = paste0(dirname(args[1]),"/")

data$Fecha_Inicio_Pago = as.POSIXct(data$Fecha_Inicio_Pago, format="%d/%m/%Y")
data$Fecha_Limite_Pago = as.POSIXct(data$Fecha_Limite_Pago, format="%d/%m/%Y")
data$Cuerpo1 = as.character(data$Cuerpo1)
data$Cuerpo2 = as.character(data$Cuerpo2)
data$Cuerpo3 = as.character(data$Cuerpo3)
data$Cuerpo4 = as.character(data$Cuerpo4)
data$Cuerpo5 = as.character(data$Cuerpo5)
data$Cuerpo6 = as.character(data$Cuerpo6)
data$Cuerpo7 = as.character(data$Cuerpo7)
 
formatearNumero <- function (x,tamanio) {
 	importe = as.character(x)
 	importe_partido = strsplit(as.character(x),"[\\,|\\.]")[[1]]
 	enteros = importe_partido[[1]]
 	decimales=0
 	if (length(importe_partido)>1) {
 		decimales = importe_partido[[2]]
 	}
 	formateado = paste0(str_pad (enteros, width = (tamanio-2), side = "left", pad = "0"),
 											str_pad(decimales, width=2, side = "right", pad = "0"))
 	return (formateado)
 }
 
formatearNumeroCuerpoBaseImponible <- function (x,tamanio) {
	print(x)
	importe = as.character(x)
 	importe_partido = strsplit(as.character(x),"[\\,|\\.]")[[1]]
 	enteros = importe_partido[[1]]
 	decimales=0
 	if (length(importe_partido)>1) {
 		decimales = importe_partido[[2]]
 	}
 	formateado = paste0(str_pad (enteros, width = (tamanio-5), side = "left", pad = "0"),",",str_pad(decimales, width=2, side = "right", pad = "0")," ")
 	if (!startsWith(enteros,"-")) {
 		formateado = paste0( formateado, " " )
 	}
 	return (formateado)
 }
 
formatearCampo <- function (valor, tamanio) {
 	if (is.na(valor)) {
 		return (str_pad ("", width = tamanio, side = "right"))
 	}
 	valor_str = as.character(valor)
 	if (str_length(valor_str) > tamanio) {
 		valor_str = substr(valor_str,1,tamanio)
 	}
 	return (str_pad (valor_str, width = tamanio, side = "right"))
 }
 
formatearFecha <- function (fecha, tamanio) {
 	str_fecha = paste0(fecha,"")
 	if (str_fecha == "") {
 		return(str_pad(str_fecha,width = tamanio, side = "right"))
 	} else {
 		return(str_pad(format(fecha, "%Y%m%d"), width = tamanio, side = "right"))
 	}
 }
 
trocearCuentaBancaria <- function (cuentaBancaria) {
 	x <- c()
 	if (is.na(cuentaBancaria) || cuentaBancaria == "") {
 		return ("")
 	} else {
        cuentaBancariaLimpia = gsub(" ", "", cuentaBancaria, fixed = TRUE);
        cuentaBancariaLimpia = gsub("-", "", cuentaBancariaLimpia, fixed = TRUE);
        if (nchar(cuentaBancariaLimpia) != 24) {
            return ("Invalid")
        } else {
                x <- c(substr(cuentaBancariaLimpia,1,2),
                             substr(cuentaBancariaLimpia,3,4),
                             substr(cuentaBancariaLimpia,5,8),
                             substr(cuentaBancariaLimpia,9,12),
                             substr(cuentaBancariaLimpia,13,14),
                             substr(cuentaBancariaLimpia,15,24)
                )
                return (x)
        }
    }
 }
 
crear_datos_generales <- function (tipo_ingreso, cod_institucion, row) {
	tipo_ingreso = formatearCampo (tipo_ingreso,8)
 	cod_institucion = formatearCampo (cod_institucion,8)
 	referencia = formatearCampo (row["Referencia_Externa"], 24)
 	fecha_inicio_actividad = formatearCampo(paste0(format(Sys.Date(), "%Y%m"),"31"),8)
 	fecha_fin_actividad = formatearCampo("40001231", 8)
 	cod_zona_recaudacion = formatearCampo("", 5)
 	cod_oficina_recaudacion = formatearCampo("", 5)
 	
 	datos_generales = paste0(tipo_ingreso,cod_institucion,referencia,fecha_inicio_actividad,fecha_fin_actividad,cod_zona_recaudacion,cod_oficina_recaudacion)
 	return (datos_generales)
 }
 
crear_datos_contribuyente <- function (row) {
 	# Datos contribuyente
 	indicador_nombre = formatearCampo("3",1)
 	personalidad = formatearCampo("F",1)
 	nombre_completo = formatearCampo(paste(toupper(row["Apellido1"]),toupper(row["Apellido2"]),toupper(row["Nombre"])),60)
 	#	nombre_completo = ""
 	nif = toupper(row["Dni"])
 	if ( nif != "" ) {
 		digito_control_nif  = formatearCampo(obtener_letra_DNI(nif),1)
 		nif = formatearCampo(limpiarNIF(nif),12)
 	} else {
 		digito_control_nif  = formatearCampo("",1)
 		nif  = formatearCampo("",12)
 	}
 	cod_pais = formatearCampo("108",3)
 	siglas_pais = "ES"
 	if (row["Dni"] != "" && detectarTipoDNI(row["Dni"]) == "NIE") {
 		siglas_pais = "EX"
 	}
 	siglas_pais=formatearCampo(siglas_pais,2)
 	datos_contribuyente = paste0(indicador_nombre,personalidad,nombre_completo,nif,digito_control_nif,cod_pais,siglas_pais)
 	return (datos_contribuyente)
 }
 
crear_datos_domiciliacion <- function (row) {
 	vectorIBAN = ""
 	# Datos domiciliación
 	if (nchar(row['Cuenta_Corriente']) > 0)
 	{
 		vectorIBAN = trocearCuentaBancaria(row['Cuenta_Corriente'])
 	}
 	if ( typeof(vectorIBAN) == "character" && vectorIBAN == "Invalid" ) {
 		return ("Invalid")
 	}
 	if ( length(vectorIBAN) == 6 ) {
 		indicador_domiciliacion = formatearCampo("1",1) # 0: NO domiciliado, 1: Domiciliado
 		indicador_nombre2 = formatearCampo("2",1) # 0:Titular es el pagador. 2: Apellidos y nombre separados. Los apellidos deben ir separados por "*" y el segundo apellido y el nombre por el carácter ",". P.e.: RODRIGUEZ*GASPAR,SILVIA.	3: Apellidos y nombre.	4: Nombre y apellidos
 		personalidad2 = formatearCampo("F",1)
 		titular_cuenta = formatearCampo(paste0(row['Apellido1_Titular'],'*',row['Apellido2_Titular'],',',row['Nombre_Titular']),60)
 		cif = formatearCampo("", 12)
 		dc_cif = formatearCampo("", 1)
 		cod_pais = formatearCampo("108", 3)
 		siglas_pais = formatearCampo(vectorIBAN[1],2)
 		cod_banco = formatearCampo(vectorIBAN[3], 4)
 		cod_oficina = formatearCampo(vectorIBAN[4], 4)
 		numero_cuenta = formatearCampo(vectorIBAN[6], 10)
 		dc_cuenta = formatearCampo(vectorIBAN[5], 2)
 		cod_referencia = formatearCampo(sprintf("%12.0f",row["Referencia_C19"]), 12)
 	} else {
 		indicador_domiciliacion = formatearCampo("0",1) # 0: NO domiciliado, 1: Domiciliado
 		indicador_nombre2 = formatearCampo("",1)
 		personalidad2 = formatearCampo("",1)
 		titular_cuenta = formatearCampo("",60)
 		cif = formatearCampo("", 12)
 		dc_cif = formatearCampo("", 1)
 		cod_pais = formatearCampo("108", 3)
 		siglas_pais = formatearCampo("ES",2)
 		cod_banco = formatearCampo("", 4)
 		cod_oficina = formatearCampo("", 4)
 		numero_cuenta = formatearCampo("", 10)
 		dc_cuenta = formatearCampo("", 2)
 		cod_referencia = formatearCampo("", 12)
 	}
 	datos_domiciliacion = paste0(indicador_domiciliacion,indicador_nombre2,personalidad2,titular_cuenta,cif,dc_cif,cod_pais,siglas_pais,cod_banco,cod_oficina,numero_cuenta,dc_cuenta,cod_referencia)
 	return (datos_domiciliacion)
 }
 
crear_datos_domicilio_fiscal <- function (row) {
 	# Datos domicilio fiscal
 	cod_pais = formatearCampo("108",3)
 	cod_provincia = formatearCampo("",2)
 	cod_municipio = formatearCampo("", 3)
 	cod_calle = formatearCampo("", 5)
 	cod_postal = formatearCampo("", 8)
 	nombre_pais = formatearCampo("ESPAÑA", 50)
 	nombre_provincia = formatearCampo("", 50)
 	nombre_municipio = formatearCampo("", 50)
 	siglas_calle = formatearCampo("",5)
 	nombre_calle = formatearCampo("", 50)
 	numero_calle = formatearCampo("", 4)
 	letra = formatearCampo("", 1)
 	numero2_calle = formatearCampo("", 4)
 	letra2 = formatearCampo("", 1)
 	indicador_km_manzana = formatearCampo("", 1)
 	numero_km_manzana = formatearCampo("", 5)
 	indicador_bloque = formatearCampo("", 1)
 	numero_bloque = formatearCampo("", 4)
 	toponimia = formatearCampo("", 25)
 	escalera = formatearCampo("", 2)
 	planta = formatearCampo("", 3)
 	puerta = formatearCampo("", 4)
 	
 	datos_domicilio_fiscal = paste0(cod_pais,cod_provincia,cod_municipio,cod_calle,cod_postal,nombre_pais,nombre_provincia,nombre_municipio,siglas_calle,nombre_calle,numero_calle,letra,numero2_calle,letra2,indicador_km_manzana,numero_km_manzana,indicador_bloque,numero_bloque,toponimia,escalera,planta,puerta)
 	return(datos_domicilio_fiscal)
 }
 
crear_datos_domicilio_tributario <- function (row) {
 	# Domicilio tributario
 	indicador_tributario_domicilio = formatearCampo ("1", 1) # 1: Sin domicilio,	2: Con domicilio sin local,	3: Con domicilio y local
 	referencia_catastral = formatearCampo ("", 14)
 	numero_cargo = formatearCampo ("", 4)
 	primer_caracter_control = formatearCampo ("", 1)
 	segundo_caracter_control = formatearCampo ("", 1)
 	referencia_catastral_manzana = formatearCampo ("", 8)
 	referencia_catastral_parcela = formatearCampo ("", 8)
 	codigo_local_ayto = formatearCampo ("", 8)
 	codigo_sublocal_ayto = formatearCampo ("", 8)
 	clave_uso = formatearCampo ("", 1)
 	clave_destino = formatearCampo ("", 3)
 	superficie_construida = formatearCampo ("", 9)
 	superficie_descubierta = formatearCampo ("", 9)
 	valor_catastral_suelo = formatearCampo ("", 12)
 	valor_catastral_construccion = formatearCampo ("", 12)
 	valor_catastral = formatearCampo ("", 12)
 	codigo_provincia = formatearCampo ("", 2)
 	codigo_municipio = formatearCampo ("", 3)
 	codigo_calle = formatearCampo ("", 5)
 	codigo_postal = formatearCampo ("48340", 8)
 	nombre_provincia = formatearCampo ("", 50)
 	nombre_municipio = formatearCampo ("", 50)
 	siglas_calle = formatearCampo ("", 5)
 	nombre_calle = formatearCampo ("", 50)
 	numero_calle = formatearCampo ("", 4)
 	letra =  formatearCampo ("", 1)
 	segundo_numero =  formatearCampo ("", 4)
 	segunda_letra = formatearCampo ("", 1)
 	indicador_km_manzana = formatearCampo ("", 1)
 	numero_km_manzana = formatearCampo ("", 5)
 	indicador_bloque = formatearCampo ("", 1)
 	descriptor_bloque = formatearCampo ("", 4)
 	toponimia = formatearCampo ("", 25)
 	escalera = formatearCampo ("", 2)
 	planta = formatearCampo ("", 3)
 	puerta = formatearCampo ("", 4)
 
 	datos_domicilio_tributario = paste0(indicador_tributario_domicilio,referencia_catastral,numero_cargo,primer_caracter_control,segundo_caracter_control,referencia_catastral_manzana,referencia_catastral_parcela,codigo_local_ayto,codigo_sublocal_ayto,clave_uso,clave_destino,superficie_construida,superficie_descubierta,valor_catastral_suelo,valor_catastral_construccion,valor_catastral,codigo_provincia,codigo_municipio,codigo_calle,codigo_postal,nombre_provincia,nombre_municipio,siglas_calle,nombre_calle,numero_calle,letra,segundo_numero,segunda_letra,indicador_km_manzana,numero_km_manzana,indicador_bloque,descriptor_bloque,toponimia,escalera,planta,puerta)	
 	return(datos_domicilio_tributario)
 }
 
crear_base_imponible <- function (row) {
	print(row)
 	importe = formatearNumeroCuerpoBaseImponible (row["Importe"], 15)
 	cuerpo1 = formatearCampo (row["Cuerpo1"], 71)
 	cuerpo2 = formatearCampo (row["Cuerpo2"], 71)
 	cuerpo3 = formatearCampo (row["Cuerpo3"], 71)
 	cuerpo4 = formatearCampo (row["Cuerpo4"], 71)
 	cuerpo5 = formatearCampo (row["Cuerpo5"], 71)
 	cuerpo6 = formatearCampo (row["Cuerpo6"], 71)
 	cuerpo7 = formatearCampo (row["Cuerpo7"], 71)
 	base_imponible = paste0(importe,cuerpo1,cuerpo2,cuerpo3,cuerpo4,cuerpo5,cuerpo6,cuerpo7)
 	datos_base_imponible = formatearCampo(base_imponible,512)
 	return(datos_base_imponible)
 }
 
 crear_final_fichero_cabecera <- function (row, i, tipo_recibo) {
 	# Campos del fichero de cabeceras de recibo (1392-etik aurrera)
 	numero_recibo = str_pad (i, width = 9, side = "left", pad = "0")
 	tipo_exaccion = formatearCampo(tipo_recibo,3)
 	codigo_remesa = formatearCampo("", 10)
 	
 	importe2 = formatearNumero(as.character(row["Importe"]), 9)
 	estado = formatearCampo("P", 1)
 	situacion = formatearCampo(estado_recibo, 1)
 	paralizado = formatearCampo("F", 1)
 	fecha_creacion = formatearFecha(Sys.Date(),8)
 	fecha_ini_voluntaria = formatearFecha(row["Fecha_Inicio_Pago"],8)
 	fecha_fin_voluntaria = formatearFecha(row["Fecha_Limite_Pago"],8)
 	fecha_cobro = formatearFecha("", 8)
 	fecha_anulacion = formatearFecha("", 8)
 	fecha_pase_ejecutiva = formatearFecha("", 8)
 	fecha_aceptacion_not_apremio = formatearFecha("", 8)
 	fecha_vencimiento_not_apremio = formatearFecha("", 8)
 	fecha_inicio_calculo_intereses = formatearFecha("", 8)
 	fecha_fin_calculo_intereses = formatearFecha("", 8)
 	fecha_nuevo_vencimiento = formatearFecha("", 8)
 	fecha_prescripcion = formatearFecha("", 8)
 	clave_cobro = formatearCampo("", 12)
 	expediente_liquidacion = formatearCampo("",10)
 	tipo_interes = formatearCampo("",5)
 	importe_intereses = formatearCampo("",9)
 	porcentage_recargo = formatearCampo("",5)
 	importe_recargo = formatearCampo("",9)
 	importe_costas = formatearCampo("",9)
 	importe_total = formatearCampo(importe2, 9)
 	indicador_domicilio_fiscal_1_campo = 	formatearCampo("F", 1)
 	domicilio_fiscal = formatearCampo("",50)
 	indicador_domicilio_tributario_1_campo = 	formatearCampo("F",1)
 	domicilio_tributario = formatearCampo("", 50)
 	
 	datos_final_linea_cabecera = paste0(numero_recibo,tipo_exaccion,codigo_remesa,importe2,estado,situacion,paralizado,fecha_creacion,fecha_ini_voluntaria,fecha_fin_voluntaria,fecha_cobro,fecha_anulacion,fecha_pase_ejecutiva,fecha_aceptacion_not_apremio,fecha_vencimiento_not_apremio,fecha_inicio_calculo_intereses,fecha_fin_calculo_intereses,fecha_nuevo_vencimiento,fecha_prescripcion,clave_cobro,expediente_liquidacion,tipo_interes,importe_intereses,porcentage_recargo,importe_recargo,importe_costas,importe_total,indicador_domicilio_fiscal_1_campo,domicilio_fiscal,indicador_domicilio_tributario_1_campo,domicilio_tributario)
 	return(datos_final_linea_cabecera)
 }
 
create_header_line_file <- function (tipo_ingreso, cod_institucion ,row, i, tipo_recibo) {
 	datos_generales = crear_datos_generales(tipo_ingreso, cod_institucion, row)
 	datos_contribuyente = crear_datos_contribuyente(row)
 	datos_domiciliacion = crear_datos_domiciliacion(row)
 	datos_domicilio_fiscal = crear_datos_domicilio_fiscal(row)
 	datos_domicilio_tributario = crear_datos_domicilio_tributario(row)
 	datos_base_imponible = crear_base_imponible(row)
 	datos_final_linea_cabecera = crear_final_fichero_cabecera(row, i, tipo_recibo)
 
 	line = paste0 (datos_generales, datos_contribuyente, datos_domiciliacion, datos_domicilio_fiscal, datos_domicilio_tributario,
 								 datos_base_imponible, datos_final_linea_cabecera)
 	return(as.character(line))
 }
 
 # num_recibo: Debe coincidir con el número de recibo en la posición 1392 del fichero rec. (empezamos por el 1 hasta el total de líneas en el EXcel) 
create_line_file_line <- function(num_recibo, num_linea, presupuesto, concepto_contable, precio, unidades) {
 	num_recibo = str_pad (num_recibo, width = 9, side = "left", pad = "0")
 	num_linea_campo = formatearCampo(num_linea, 2)
 	presupuesto_campo = formatearCampo(presupuesto, 4)
 	concepto_contable_campo = formatearCampo(concepto_contable, 5)
 	precio_campo = formatearCampo("0", 10)
 	unidades_campo = formatearCampo(unidades, 12)
 	importe_campo = formatearNumero(precio, 9)
 	numero_fraccion = formatearCampo("", 2)
 	
 	line = paste0(num_recibo,num_linea_campo,presupuesto_campo,concepto_contable_campo,precio_campo,unidades_campo,importe_campo,numero_fraccion)
 	return (as.character(line))
 }
 
header_lines = data.frame()
linea_lines = data.frame()

i=1
error = FALSE
for ( i in 1:nrow(data) ) {
	row = data[i,]
	header_line = create_header_line_file(row["Tipo_Ingreso"], row["Institucion"], row, i, tipo_recibo)
	if ( grepl('Invalid',header_line,fixed = TRUE) ) {
		print ("Hay errores en los datos de domiciliación. Se cancela el proceso. El registro afectado es: ")
#		print (row)
		print ("Por favor, corrija los errores antes de volver a lanzar el proceso")
		error = TRUE
		print("===============================================================")
		print("El proceso ha FALLADO")
		print("===============================================================")
		quit(status=1)
	}
	linea_line = create_line_file_line(i,"1", row["Presupuesto"], row["Tributo"],row["Importe"],"0")

	header_lines = rbind(header_lines,as.character(header_line))
	colnames(header_lines) = c("line")
	header_lines["line"] = as.character(header_lines$line)

	linea_lines = rbind(linea_lines,as.character(linea_line))
	colnames(linea_lines) = c("line")
	linea_lines["line"] = as.character(linea_lines$line)
}
if (!error) {
	print("Ficheros generados:")
	print(paste0(fichero_sin_extension,".rec"))
	print(paste0(fichero_sin_extension,".lin"))
	write.table(header_lines, paste0(path,fichero_sin_extension,".rec"),quote = FALSE,col.names = FALSE, row.names = FALSE, fileEncoding="Windows-1252", eol = "\r\n")
	write.table(linea_lines, paste0(path,fichero_sin_extension,".lin"),quote = FALSE,col.names = FALSE, row.names = FALSE, fileEncoding="Windows-1252", eol = "\r\n")
	print("===============================================================")
	print("El proceso ha finalizado correctamente")
	print("===============================================================")
	quit(status=0)
}
