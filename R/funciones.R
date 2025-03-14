
trim <- function (x) gsub("^\\s+|\\s+$", "", x)

#df <- int992NO99
#flagActualizables = TRUE
# Buscar los registros actualizables o NO actualizables
# IMPORTANTE: No funciona correctamente cuando hay muchos registros para un mismo NIF. Sacamos los cambios cruzados.
buscar_actualizables = function (df, flagActualizables) {
	df$FULLNAME_LIMPIO = trim(df$FULLNAME_LIMPIO)
	repes_count <- df %>% select(FULLNAME_LIMPIO) %>%  group_by(FULLNAME_LIMPIO) %>% summarize( num_registros_por_fullname = n() )
	df_repes_count <- merge(repes_count,df)
	# Contamos cuantos nifs distintos hay para cada nombre completo
	repes_nif_distinct_count <- df_repes_count %>% group_by (FULLNAME_LIMPIO) %>% summarize( count_distinct_nif_limpio_destino = n_distinct(NIF_LIMPIO_DESTINO), count_distinct_nif_sucio_destino = n_distinct(NIF_DESTINO))
	# Lo volvemos a juntar con el anterior
	df_repes_count <- merge(repes_nif_distinct_count, df_repes_count)

	# Estos son conflictivos si tienen distinto DBOID_DESTINO
	df_mismo_origen_mismo_destinonregs <- df_repes_count %>% filter( count_distinct_nif_limpio_destino == 1 & count_distinct_nif_sucio_destino == 1 & num_registros_por_fullname == 2 )
	
	
	df_mismo_origen_mismo_destinonregs_distinct_DBOID_DEST <- df_mismo_origen_mismo_destinonregs %>% group_by(NIF_DESTINO) %>% summarise( distinct_DBOID_DEST=n_distinct(DBOID_DESTINO) )	
	
	df_mismo_origen_mismo_destinonregs_distinct_DBOID_DEST <- merge(df_repes_count, df_mismo_origen_mismo_destinonregs_distinct_DBOID_DEST)
	
	# No actualizables porque tiene 2 destinos
	conflictivos <-  df_mismo_origen_mismo_destinonregs_distinct_DBOID_DEST %>% filter( distinct_DBOID_DEST > 1 )
	
	noconflictivos <-  df_mismo_origen_mismo_destinonregs_distinct_DBOID_DEST %>% filter( distinct_DBOID_DEST == 1 ) 
	
	noconflictivos <- noconflictivos %>% select(-distinct_DBOID_DEST) 
	
	df_repes_count <- df_repes_count %>% filter( !(count_distinct_nif_limpio_destino == 1 & count_distinct_nif_sucio_destino == 1 & num_registros_por_fullname == 2) )
	
	df_repes_count <- rbind(df_repes_count,noconflictivos)
	
	# Si sólo hay uno distinto es actualizable
	df_repes_count <- mutate(df_repes_count, mismo_nif = ifelse (count_distinct_nif_limpio_destino == 1, TRUE, FALSE) )

	repes_decision <- df_repes_count %>% group_by (NIF_LIMPIO_DESTINO) %>% summarize( count_distinct_nif_sucio = n_distinct(NIF_DESTINO) ) %>% distinct()

	# Actualizables automáticamente
	nifs_sucios_unicos <- repes_decision %>% filter ( count_distinct_nif_sucio == 1 ) 
	df_nifs_sucios_unicos <- merge(df_repes_count, nifs_sucios_unicos, by="NIF_LIMPIO_DESTINO") 
	
	# NO Actualizables automáticamente. Hay que elegir cual de los NIFs sucios es el bueno.
	nifs_sucios_repes <- repes_decision %>% filter ( count_distinct_nif_sucio > 1 ) %>% distinct()
	
	nif_distintos = nifs_sucios_repes %>% select( NIF_LIMPIO_DESTINO ) %>% distinct()
	
	tot_nif_distintos = nrow(nifs_sucios_repes)
	
	# Agrupamos por NIF limpio.
	nifs_sucios_repes_merge <- merge(nifs_sucios_repes, df_repes_count, by="NIF_LIMPIO_DESTINO")
	
	# Calculamos la longitud del NIF. Porque los USICAL nuevos tienen el NIF más largo.
	nifs_sucios_repes_merge$LENGTH_NIF_DESTINO <- str_length(as.character(nifs_sucios_repes_merge$NIF_DESTINO))

	# Agrupamos por NIF limpio y buscamos el más largo, pueso que los buenos de USICAL empiezan por ES y són más largos que los demás.
	nifs_sucios_max_length_nif_destino <- nifs_sucios_repes_merge %>% group_by (NIF_LIMPIO_DESTINO ) %>% summarise( MAX_LENGTH_NIF_DESTINO = max(LENGTH_NIF_DESTINO) )
	
	df_nifs_sucios_max_length <- merge(nifs_sucios_repes_merge, nifs_sucios_max_length_nif_destino, by="NIF_LIMPIO_DESTINO")
	
	# Elegimos los NIFs más largos como buenos.
	df_repes_actualizables = df_nifs_sucios_max_length %>% filter( LENGTH_NIF_DESTINO == MAX_LENGTH_NIF_DESTINO )
	
	# Cogemos los que sólo tienen NIF destino como actualizables.
	actualizables_unicos <- df_nifs_sucios_unicos %>% filter( mismo_nif == flagActualizables )
	names = colnames(actualizables_unicos)
	# Cogemos los repes con el NIF más largo como actualizables.
	actualizables_repes <- df_repes_actualizables %>% select ( names ) %>% filter( mismo_nif == flagActualizables )
	
	# Dataframe con todos los actualizables.
	df_actualizables = rbind (actualizables_unicos, actualizables_repes)
	# Dataframe con todos los NO actualizables.
	NO_actualizables <- df_repes_count %>% filter( mismo_nif == flagActualizables )

	# Quitamos las columnas que no estaban en el data.frame original.
	df_actualizables <- df_actualizables %>% select ( -c(mismo_nif, num_registros_por_fullname, count_distinct_nif_limpio_destino, count_distinct_nif_sucio_destino, count_distinct_nif_sucio) ) %>% distinct()
	
	# Esto hay que repasarlo cuando haya conflictivos
	if ( nrow(conflictivos) > 0 ) {
		conflictivos <- conflictivos %>% mutate(
			DBOID_NUMBER = gsub(pattern="^P[\\*](.*)$", "\\1", DBOID_DESTINO)
		)

		conflictivos_MIN_DBOID <- conflictivos %>% group_by( NIF_LIMPIO_DESTINO ) %>% summarise( MIN_DBOID = min(DBOID_NUMBER) )

		conflictivos <- merge (conflictivos, conflictivos_MIN_DBOID, by="NIF_LIMPIO_DESTINO")

		conflictivos <- conflictivos %>% filter ( DBOID_DESTINO == MINDBOID )

		names = colnames(actualizables)
		conflictivos <- conflictivos %>% select ( names )

		actualizables <- rbind(actualizables, conflictivos)
	}
	NO_actualizables <- NO_actualizables %>% select ( -c(mismo_nif, num_registros_por_fullname, count_distinct_nif_limpio_destino, count_distinct_nif_sucio_destino) ) %>% distinct()
	
	# Dependiendo del parámetro de entrada devolvemos o los actualizables o los NO actualizables.
	if ( flagActualizables ) {
		return (df_actualizables)
	} else {
		return (NO_actualizables)
	}
}

#int992NO99_actualizables = actualizables(int992NO99, TRUE)
#int992NO99_NO_actualizables = actualizables(int992NO99, FALSE)

limpiarNombreCompleto = function (x) {
	if (is.na(x)) return(NA)
	y = x
	# Quitamos SA|SL|SAU del final tanto con puntos como sin puntos
	y = gsub ("^(.*)(( S\\.?L\\.?)|(\\ S\\.?A\\.?))$", "\\1", x )
	y = gsub ("^(.*)(( S\\.?A\\.?U\\.?))$", "\\1", y )
	y = gsub ("^(.*)(( S\\.?L\\.?L\\.?))$", "\\1", y )
	y = str_replace_all(y, "\\ ", "")
	y = str_replace_all(y, "\\,", "")
	y = str_replace_all(y, "\\.", "")
	y = str_replace_all(y, "\\-", "")
	y = str_replace_all(y, "\\*", "")
	y = str_replace_all(y, "(\\S\\.[A|L]\\.?)", "")
	return (y)
}

limpiarNIF = function (x) {
	if (is.na(x)) return(NA)
	y = x
	# Si tiene letra al final se lo quitamos
	y = gsub("^(.*)([[:alpha:]])$","\\1", y)
	# Si empieza por ES se lo quitamos
	y = gsub("^(ES)(.*)([[:alpha:]]?)$","\\2", y)
	y = gsub("^(0)?(.{8})$","\\2", y)
	y = gsub("^([XYZ])(0)([[:digit:]]{7})$","\\1\\3", y)
	# En caso de que sea un CIF, le quitamos la letra final para comparar
	y = gsub("^([A-H|J-N|P-S|U-W])([0-9]{2})([0-9]{5})(.)$","\\1\\2\\3", y)
	# Quitar los ceros al principio
	y = gsub("^[0]+","",y)
	return (y)
}

tamano <- function (x) {
	x_size=object.size(x)
	return (format(x, units = "auto", standard = "SI"))
}

# Para hacer bien la comparación calculamos la letra del DNI o NIE, cuando es uno correcto

nif_valido = function(x) {
	if ( is.na(x) )
		return(NA)
	if ( str_detect(x, "^([0-9]{8}[A-Z]?)$|^([0-9]{7}[A-Z]?)$|^[XYZ][0-9]{7}[A-Z]?$" ) )
		return(TRUE)
	else
		return(FALSE)
}

detectarTipoDNI <- function(x) {
	if ( !nif_valido(x) ) {
		return ("Invalido")
	} else if ( str_detect(x, "^([0-9]{8}[A-Z]?)$|^([0-9]{7}[A-Z]?)$" ) ) {
			return ("DNI")
	} else {
			return ("NIE")
	}
}

cif_valido = function(x) {
	if ( is.na(x) )
		return(NA)
	conletra = ifelse ( substr(x,9,9) == "", FALSE, TRUE )
	letra = substr(x,9,9)
	if ( conletra == TRUE ) {
		if ( str_length(x) != 9 )
			return (FALSE)
		if ( str_detect(x, "^([A-H|J-N|P-S|U-W])([0-9]{2})([0-9]{5})(.)$" ) ) {
			# letra_calculada = calcula_letra_cif(x)
			# if ( !is.na(letra_calculada) ) {
			# 	return (ifelse (( letra_calculada == letra), TRUE, FALSE ))
			# }	else { return (NA) }
			return (TRUE)
		}	else return(FALSE)
	} else {
		if (str_length(x) != 8 )
			return (FALSE)
		if ( str_detect(x, "^([A-H|J-N|P-S|U-W])([0-9]{2})([0-9]{5})$" ) )
			return(TRUE)
		else
			return(FALSE)
	}
}

obtener_digito_control_cif = function(x) {
	if ( is.na(x) )
		return(NA)
		letra = substr(x,9,9)
		return(letra)
}

calcula_letra_cif = function(x) {
	if ( is.na(x) ) return(NA)
	if (!cif_valido(x)) {
		return('Invalido')
	}
	letra = substr(x,1,1)
	numero = substr(x,2,8)
	letras = c("J","A","B","C","D","E","F","G","H","I","J")
	A = as.numeric(substr(numero,2,2)) + as.numeric(substr(numero,4,4)) + as.numeric(substr(numero,6,6))
	sum_impares1 = as.numeric(substr(numero,1,1)) * 2
	sum_impares1 = ifelse ( sum_impares1 >= 10 , 1 + (sum_impares1-10), sum_impares1 ) 
	sum_impares2 = as.numeric(substr(numero,3,3)) * 2
	sum_impares2 = ifelse ( sum_impares2 >= 10 , 1 + (sum_impares2-10), sum_impares2 ) 
	sum_impares3 = as.numeric(substr(numero,5,5)) * 2
	sum_impares3 = ifelse ( sum_impares3 >= 10 , 1 + (sum_impares3-10), sum_impares3 )
	sum_impares4 = as.numeric(substr(numero,7,7)) * 2
	sum_impares4 = ifelse ( sum_impares4 >= 10 , 1 + (sum_impares4-10), sum_impares4 )
	B =  sum_impares1 + sum_impares2 + sum_impares3 + sum_impares4
	C = A + B
	cc = as.character(C)
	D = 10 - as.numeric(substr(cc,str_length(cc),str_length(cc)))
	if ( D == 10 ) { D = 0 }
	if (str_detect(letra,"^[A-H]|J|[U-V]$")) {
		return (paste0("",D))
	}	else {
		# Sumamos uno porque los arrays en R empiezan en 1 no en cero
		D = D + 1
		return (paste0("",letras[D]))
	}
}

calcula_letra_dni = function( x ) {
	Letradni = c("T","R","W","A","G","M","Y","F","P","D","X","B","N","J","Z","S","Q","V","H","L","C","K","E")
	if (is.na(x)) return(NA)
	if (!nif_valido(x)) {
		return('Invalido')
	}
	y = as.character(x)
	
	y = gsub("^([XYZ]?)([0-9]+)([[:alpha:]]?)$","\\1\\2",y)
	
	if (str_detect(as.character(y),"^[XYZ]([0-9]+)$")) { 
		y = str_replace(y, "[X]", "0")
		y = str_replace(y, "[Y]", "1")
		y = str_replace(y, "[Z]", "2")
	}
	
	if ( is.numeric(as.numeric(y)) && nchar(y) > 0 ) {
		y = as.numeric(y)
		resto = y %% 23
		letra = Letradni[[resto + 1]]
	} else letra = ''
	return (letra)
}

obtener_numero_DNI = function (x) {
	if (is.na(x)) return(NA)
	if ( regexpr ("^([0-9]{8})([[:alpha:]])$", x) != -1 ) {
		return(gsub("^([0-9]{8})([[:alpha:]])$", "\\1",x))
	}
	else return (x)
}

obtener_letra_DNI = function (x) {
	if (is.na(x)) return(NA)
	if (!nif_valido(x)) return ("")
	if ( regexpr ("^([0-9]{7})([[:alpha:]])$", x) != -1 ) {
		return(gsub("^([0-9]{7})([[:alpha:]])$", "\\2",x))
	}
	if ( regexpr ("^([0-9]{8})([[:alpha:]])$", x) != -1 ) {
		print("hau da kasua")
		print(gsub("^([0-9]{8})([[:alpha:]])$", "\\2",x))
		return(gsub("^([0-9]{8})([[:alpha:]])$", "\\2",x))
	} else if (regexpr ("^([XYZ][0-9]{7})([A-Z])?$", x) != -1 ) {
		return(gsub("^([XYZ][0-9]{7})([A-Z])?$", "\\2",x))
	}
	else return (x)
}
