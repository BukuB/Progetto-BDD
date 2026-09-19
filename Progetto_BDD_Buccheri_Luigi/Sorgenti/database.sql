DROP DATABASE IF EXISTS progetto;
CREATE DATABASE progetto;
USE progetto;

-- --------------------------------------------------------------------------------------------------
																		-- CREAZIONE DELLE TABELLE
-- --------------------------------------------------------------------------------------------------
CREATE TABLE UTENTE (
	username VARCHAR(100) UNIQUE,
    password VARCHAR(100),
    cf VARCHAR(100) PRIMARY KEY,
    data_nascita DATE,
    luogo_nascita VARCHAR(100),
    ruolo_interno VARCHAR(50),
    professione VARCHAR(100)
)ENGINE=InnoDB;

CREATE TABLE RECAPITO_EMAIL (
	indirizzo_email VARCHAR(100) UNIQUE PRIMARY KEY,
    cf VARCHAR(100),
    FOREIGN KEY (cf) 
		REFERENCES UTENTE(cf)
)ENGINE=InnoDB;

CREATE TABLE  AMMINISTRATORE (
	cf VARCHAR(100) PRIMARY KEY,
    FOREIGN KEY (cf) 
		REFERENCES UTENTE(cf)
)ENGINE=InnoDB;

CREATE TABLE  REVISORE_ESG (
	numero_revisioni INT,
    indice_affidabilita INT,
    cf VARCHAR(100) PRIMARY KEY,
    FOREIGN KEY (cf) 
		REFERENCES UTENTE(cf)
)ENGINE=InnoDB;

CREATE TABLE  RESPONSABILE_AZIENDALE (
	cf VARCHAR(100) PRIMARY KEY,
    FOREIGN KEY (cf) 
		REFERENCES UTENTE(cf),
	curriculum_vitae VARCHAR(100),
	CONSTRAINT controllo_cv_pdf CHECK (curriculum_vitae LIKE '%.pdf')
)ENGINE=InnoDB;

CREATE TABLE  COMPETENZA (
	 nome VARCHAR(100) PRIMARY KEY
)ENGINE=InnoDB;

CREATE TABLE  AZIENDA (
	nome VARCHAR(100) UNIQUE,
    ragione_sociale VARCHAR(100),
    partita_IVA VARCHAR(100) PRIMARY KEY,
    settore VARCHAR(100),
    numero_dipendenti INT DEFAULT 0,
    logo VARCHAR(100),
    numero_bilanci INT,
    cf VARCHAR(100),
    FOREIGN KEY (cf) 
		REFERENCES RESPONSABILE_AZIENDALE(cf)
)ENGINE=InnoDB;

CREATE TABLE VOCE_CONTABILE(
	nome VARCHAR(100) PRIMARY KEY, 
	descrizione_testuale VARCHAR(255),
    cf VARCHAR(100),
    FOREIGN KEY (cf) 
		REFERENCES AMMINISTRATORE(cf)
)ENGINE=InnoDB;

CREATE TABLE BILANCIO_DI_ESERCIZIO (
	id INT AUTO_INCREMENT PRIMARY KEY,
    data_creazione DATE,
    stato ENUM('bozza', 'in revisione', 'approvato', 'respinto') DEFAULT 'bozza',
    partita_IVA VARCHAR(100),
    FOREIGN KEY (partita_IVA) 
		REFERENCES AZIENDA(partita_IVA)
)ENGINE=InnoDB;

CREATE TABLE INDICATORE_ESG (
	nome VARCHAR(100) PRIMARY KEY,
    immagine VARCHAR(100),
    rilevanza INT,
    cf VARCHAR(100),
    FOREIGN KEY (cf) 
		REFERENCES AMMINISTRATORE(cf),
	CONSTRAINT controllo_rilevanza CHECK (rilevanza >= 0 AND rilevanza <= 10)
)ENGINE=InnoDB;

CREATE TABLE AMBIENTALI (
	codice_normativa VARCHAR(100),
    nome_indicatore VARCHAR(100) PRIMARY KEY,
    FOREIGN KEY (nome_indicatore) 
		REFERENCES INDICATORE_ESG(nome)
)ENGINE=InnoDB;

CREATE TABLE SOCIALI (
	ambito_sociale VARCHAR(100),
    frequenza_rilevazione VARCHAR(100),
    nome_indicatore VARCHAR(100) PRIMARY KEY,
    FOREIGN KEY (nome_indicatore) 
		REFERENCES INDICATORE_ESG(nome)
)ENGINE=InnoDB;

CREATE TABLE LIVELLO_COMPETENZA (
	livello INT,
    cf VARCHAR(100),
    FOREIGN KEY (cf) 
		REFERENCES REVISORE_ESG(cf),
	nome_competenza VARCHAR(100), 
	FOREIGN KEY (nome_competenza) 
		REFERENCES COMPETENZA(nome),
	CONSTRAINT controllo_livello CHECK (livello >= 0 AND livello <= 5),
    PRIMARY KEY(livello, cf, nome_competenza)
)ENGINE=InnoDB;

CREATE TABLE COMPOSIZIONE_BILANCIO (
	valore_numerico INT,
    nome_voce_contabile VARCHAR(100), 
    FOREIGN KEY (nome_voce_contabile) 
		REFERENCES VOCE_CONTABILE(nome),
	id_bilancio INT,
    FOREIGN KEY (id_bilancio) 
		REFERENCES BILANCIO_DI_ESERCIZIO(id),
	PRIMARY KEY(id_bilancio, nome_voce_contabile)
)ENGINE=InnoDB;

CREATE TABLE GIUDIZIO (
	esito ENUM('approvazione', 'approvazione con rilievi', 'respingimento'), 
    data DATE,
    campo_rilievi TEXT,
    cf VARCHAR(100),
    FOREIGN KEY (cf) 
		REFERENCES REVISORE_ESG(cf),
	id_bilancio INT,
    FOREIGN KEY (id_bilancio) 
		REFERENCES BILANCIO_DI_ESERCIZIO(id),
	PRIMARY KEY(cf, id_bilancio)
)ENGINE=InnoDB;

CREATE TABLE ASSOCIAZIONE_INDICATORE_ESG (
	valore_indicatore INT,
    fonte VARCHAR(100),
    data_rilevazione DATE,
    id_bilancio INT,
    FOREIGN KEY (id_bilancio) 
		REFERENCES BILANCIO_DI_ESERCIZIO(id),
	nome_voce_contabile VARCHAR(100), 
    FOREIGN KEY (nome_voce_contabile) 
		REFERENCES VOCE_CONTABILE(nome),
	nome_indicatore VARCHAR(100),
    FOREIGN KEY (nome_indicatore) 
		REFERENCES INDICATORE_ESG(nome),
	PRIMARY KEY(id_bilancio, nome_voce_contabile, nome_indicatore)
)ENGINE=InnoDB;

CREATE TABLE NOTA (
	data DATE,
    campo_testo TEXT,
    cf VARCHAR(100),
    FOREIGN KEY (cf) 
		REFERENCES REVISORE_ESG(cf),
	id_bilancio INT,
    FOREIGN KEY (id_bilancio) 
		REFERENCES BILANCIO_DI_ESERCIZIO(id),
	nome_voce_contabile VARCHAR(100), 
    FOREIGN KEY (nome_voce_contabile) 
		REFERENCES VOCE_CONTABILE(nome),
	PRIMARY KEY(cf, id_bilancio, nome_voce_contabile)
)ENGINE=InnoDB;
-- --------------------------------------------------------------------------------------------------
																						-- VISTE
-- --------------------------------------------------------------------------------------------------
CREATE VIEW NumeroAziendeRegistrate AS
	SELECT COUNT(*) AS TotaleAziende
    FROM AZIENDA;
-- --------------------------------------------------------------------------------------------------
CREATE VIEW NumeroRevisoreRegistrati AS 
	SELECT COUNT(*) AS TotaleRevisori
    FROM REVISORE_ESG;
-- --------------------------------------------------------------------------------------------------
-- Prima vista per memorizzare quanti giudizi ha ricevuto ogni azienda
CREATE VIEW TotaleGiudiziAzienda AS
	SELECT A.partita_IVA, A.nome, COUNT(*) AS Totale
	FROM AZIENDA A
	JOIN BILANCIO_DI_ESERCIZIO B ON A.partita_IVA = B.partita_iva
	JOIN GIUDIZIO G ON B.id = G.id_bilancio
	GROUP BY A.partita_IVA, A.nome;
-- Seconda vista per memorizzare quante approvazioni ha ricevuto un azienda
CREATE VIEW ApprovazioniAzienda AS
	SELECT A.partita_IVA, COUNT(*) AS Approvati
	FROM AZIENDA A
	JOIN BILANCIO_DI_ESERCIZIO B ON A.partita_IVA = B.partita_iva
	JOIN GIUDIZIO G ON B.id = G.id_bilancio
	WHERE G.esito = 'approvazione'
	GROUP BY A.partita_IVA;
-- Terza vista calcola la percentuale di approvazione con i risultati ottenuti nelle viste prima
CREATE VIEW AziendaPiuAffidabile AS
	SELECT T.nome AS NomeAzienda, (A.Approvati * 100 / T.Totale) AS PercentualeAffidabilita
	FROM TotaleGiudiziAzienda T
	JOIN ApprovazioniAzienda A ON T.partita_IVA = A.partita_IVA
	ORDER BY PercentualeAffidabilita DESC
	LIMIT 1;
-- --------------------------------------------------------------------------------------------------
CREATE VIEW TotaleAssociazioniIndicatoriPerBilancio AS
	SELECT B.id AS ID_Bilancio, COUNT(*) AS TotaleIndicatori
    FROM BILANCIO_DI_ESERCIZIO B
    JOIN ASSOCIAZIONE_INDICATORE_ESG A ON B.id = A.id_bilancio
    GROUP BY B.id
    ORDER BY TotaleIndicatori DESC;
-- --------------------------------------------------------------------------------------------------
																				-- STORED PROCEDURE 
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE PROCEDURE RegistrazioneUtente(
    IN par_email VARCHAR(100),
    IN par_username VARCHAR(100), 
    IN par_password VARCHAR(100), 
    IN par_cf VARCHAR(100), 
    IN par_data_nascita DATE, 
    IN par_luogo_nascita VARCHAR(100), 
    IN par_ruolo_interno VARCHAR(100), 
    IN par_curriculum_vitae VARCHAR(255),
    IN par_professione VARCHAR(100)
)
BEGIN
    INSERT INTO UTENTE(username, password, cf, data_nascita, luogo_nascita, ruolo_interno, professione)
    VALUES (par_username, par_password, par_cf, par_data_nascita, par_luogo_nascita, par_ruolo_interno, par_professione);
    
    INSERT INTO RECAPITO_EMAIL(indirizzo_email, cf)
    VALUES (par_email, par_cf);
    
    IF par_professione = 'amministratore' THEN
        INSERT INTO AMMINISTRATORE(cf)
        VALUES (par_cf);
    
    ELSEIF par_professione = 'revisore_esg' THEN
        INSERT INTO REVISORE_ESG(cf)
        VALUES (par_cf);
    
    ELSEIF par_professione = 'responsabile_aziendale' THEN
        INSERT INTO RESPONSABILE_AZIENDALE(cf, curriculum_vitae)
        VALUES (par_cf, par_curriculum_vitae);
    
    END IF;
END $
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE PROCEDURE AccessoUtente(
	IN par_username VARCHAR(100), 
    IN par_password VARCHAR(100))
    
BEGIN
	DECLARE ControlloEsistenzaUsername INT;
    DECLARE ControlloPassword VARCHAR(255);
    
    SET ControlloEsistenzaUsername = (SELECT COUNT(*)
									  FROM UTENTE
                                      WHERE username = par_username);
	
    IF (ControlloEsistenzaUsername = 1) THEN
    
		SET ControlloPassword = (SELECT password
								 FROM UTENTE
								 WHERE username = par_username);
                                      
		IF ControlloPassword = par_password THEN
        
			SELECT 'Login effettuato con successo' AS Esito, cf, username 
            FROM UTENTE 
            WHERE username = par_username;
        ELSE
            SELECT 'Errore: Password errata' AS Esito;
        END IF;
        
    ELSE
        SELECT 'Errore: Username non trovato' AS Esito;
    END IF;

END $
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE PROCEDURE PopolamentoIndEsg(
    IN par_nome VARCHAR(100), 
    IN par_immagine VARCHAR(100), 
    IN par_rilevanza INT,
    IN par_cf VARCHAR(100),
    IN par_tipo VARCHAR(100),
    IN par_codice_normativa VARCHAR(100),
    IN par_ambito_sociale VARCHAR(100),
    IN par_frequenza_rilevazione VARCHAR(100)
)
BEGIN

    DECLARE ControlloCf INT;
    
    SET ControlloCf = (SELECT COUNT(*)
                       FROM AMMINISTRATORE
                       WHERE cf = par_cf);
                       
    IF ControlloCf = 1 THEN
        
        SELECT 'Autorizzato: Indicatore inserito con successo' AS Esito, cf
        FROM AMMINISTRATORE
        WHERE cf = par_cf;
        
        INSERT INTO INDICATORE_ESG(nome, immagine, rilevanza, cf)
        VALUES (par_nome, par_immagine, par_rilevanza, par_cf);
    
        IF par_tipo = 'ambientali' THEN
            INSERT INTO AMBIENTALI(nome_indicatore, codice_normativa)
            VALUES (par_nome, par_codice_normativa);
    
        ELSEIF par_tipo = 'sociali' THEN
            INSERT INTO SOCIALI(nome_indicatore, ambito_sociale, frequenza_rilevazione)
            VALUES (par_nome, par_ambito_sociale, par_frequenza_rilevazione);
        
        END IF; 
        
    ELSE
        SELECT 'Non Autorizzato: Solo gli amministratori possono inserire gli indicatori' AS Esito;
        
    END IF; 
END $
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE PROCEDURE CreazioneTemplate(
	IN par_nome VARCHAR(100), 
	IN par_descrizione_testuale VARCHAR(255),
	IN par_cf VARCHAR(100))

BEGIN

    DECLARE ControlloCf INT;
    
    SET ControlloCf = (SELECT COUNT(*)
                       FROM AMMINISTRATORE
                       WHERE cf = par_cf);
                       
    IF ControlloCf = 1 THEN
        
        SELECT 'Autorizzato: Voce contabile inserita nel template' AS Esito, cf
        FROM AMMINISTRATORE
        WHERE cf = par_cf;
        
        INSERT INTO VOCE_CONTABILE(nome, descrizione_testuale, cf)
        VALUES (par_nome, par_descrizione_testuale, par_cf);
        
    ELSE
        SELECT 'Non Autorizzato: Solo gli amministratori possono creare il template' AS Esito;
        
    END IF; 
END $
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
DELIMITER $

CREATE PROCEDURE AssociaRevisore(
    IN par_cf_admin VARCHAR(100),
    IN par_cf_revisore VARCHAR(100),
    IN par_id_bilancio INT
)
BEGIN
    DECLARE ControlloAdmin INT;
    
    SET ControlloAdmin = (SELECT COUNT(*)
                          FROM AMMINISTRATORE
                          WHERE cf = par_cf_admin);
    
    IF ControlloAdmin = 1 THEN 
        SELECT 'Autorizzato: Revisore associato al bilancio con successo' AS Esito;
        
        INSERT INTO GIUDIZIO(cf, id_bilancio)
        VALUES (par_cf_revisore, par_id_bilancio);
    
    ELSE
        SELECT 'Non Autorizzato: Solo gli amministratori possono assegnare i bilanci' AS Esito;
    
    END IF;
END $
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE PROCEDURE CreaCompetenza(
    IN par_nome_competenza VARCHAR(100),
    IN par_cf_revisore VARCHAR(100),
    IN par_livello INT)

BEGIN
    DECLARE ControlloCf INT;
    DECLARE ControlloEsistenzaCompetenza INT;
    DECLARE ControlloAssociazioneEsistente INT;

    SET ControlloCf = (SELECT COUNT(*) 
                       FROM REVISORE_ESG 
                       WHERE cf = par_cf_revisore);

    IF ControlloCf = 1 THEN

        SET ControlloEsistenzaCompetenza = (SELECT COUNT(*) 
                                            FROM COMPETENZA 
                                            WHERE nome = par_nome_competenza);
        
        IF ControlloEsistenzaCompetenza = 0 THEN
            INSERT INTO COMPETENZA(nome) VALUES (par_nome_competenza);
        END IF;

        SET ControlloAssociazioneEsistente = (SELECT COUNT(*) 
                                              FROM LIVELLO_COMPETENZA 
                                              WHERE cf = par_cf_revisore AND nome_competenza = par_nome_competenza);

        IF ControlloAssociazioneEsistente = 1 THEN
            UPDATE LIVELLO_COMPETENZA 
            SET livello = par_livello 
            WHERE cf = par_cf_revisore AND nome_competenza = par_nome_competenza;
            
            SELECT 'Autorizzato: Livello competenza aggiornato' AS Esito;
        ELSE
            INSERT INTO LIVELLO_COMPETENZA(cf, nome_competenza, livello)
            VALUES (par_cf_revisore, par_nome_competenza, par_livello);
            
            SELECT 'Autorizzato: Nuova competenza inserita con successo' AS Esito;
        END IF;

    ELSE
        SELECT 'Non Autorizzato: Solo i revisori possono gestire competenze' AS Esito;
    END IF;
END$
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE PROCEDURE CreaNota(
	IN par_data DATE, 
	IN par_campo_testo TEXT, 
	IN par_cf VARCHAR(100), 
	IN par_id_bilancio INT,
    IN par_nome_voce_contabile VARCHAR(100))

BEGIN 
	DECLARE ControlloCf INT;
    DECLARE ControlloEsistenzaBilancio INT;
    
    SET ControlloCf = (SELECT COUNT(*)
					   FROM REVISORE_ESG
                       WHERE cf = par_cf);
                       
	IF ControlloCf = 1 THEN
		SELECT 'Autorizzato all inserimento della nota' AS Esito
        FROM REVISORE_ESG
        WHERE cf = par_cf;
    
		SET ControlloEsistenzaBilancio = (SELECT COUNT(*)
											FROM BILANCIO_DI_ESERCIZIO
											WHERE id = par_id_bilancio);
	
		IF ControlloEsistenzaBilancio = 1 THEN
			INSERT INTO NOTA(data, campo_testo, cf, id_bilancio, nome_voce_contabile)
            VALUES (par_data, par_campo_testo, par_cf, par_id_bilancio, par_nome_voce_contabile);
            SELECT 'Autorizzato: Nota inserita con successo' AS Esito;
		
        ELSE 
			SELECT 'Errore: Non esiste un bilancio con questo ID' AS Esito;
		
        END IF;
	
    ELSE
		SELECT 'Non Autorizzato: Solo i revisori ESG possono inserire note' AS Esito;
	END IF;
END$
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE PROCEDURE CreaGiudizio(
	IN par_esito VARCHAR(100), 
	IN par_data DATE, 
	IN par_campo_rilievi TEXT, 
	IN par_cf VARCHAR(100), 
	IN par_id_bilancio INT)

BEGIN
	DECLARE ControlloCf INT;
    DECLARE ControlloAssegnazione INT;

    SET ControlloCf = (SELECT COUNT(*)
					   FROM REVISORE_ESG
                       WHERE cf = par_cf);
                       
	IF ControlloCf = 1 THEN

		SET ControlloAssegnazione = (SELECT COUNT(*)
                                     FROM GIUDIZIO
                                     WHERE cf = par_cf AND id_bilancio = par_id_bilancio);
	
		IF ControlloAssegnazione = 1 THEN
            UPDATE GIUDIZIO
            SET 
                esito = par_esito, 
                data = par_data, 
                campo_rilievi = par_campo_rilievi
            WHERE cf = par_cf AND id_bilancio = par_id_bilancio;
            
            SELECT 'Autorizzato: Giudizio inserito con successo' AS Esito;
			
		ELSE 
			SELECT 'Errore: Non sei stato assegnato a questo bilancio o il bilancio non esiste' AS Esito;
        END IF;
	
    ELSE
		SELECT 'Non Autorizzato: Solo i revisori ESG possono inserire giudizi' AS Esito;
	END IF;
END $
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE PROCEDURE RegistrazioneAzienda(
	IN 	par_nome VARCHAR(100),
    IN par_ragione_sociale VARCHAR(100),
    IN par_partita_iva VARCHAR(100),
    IN par_settore VARCHAR(100),
    IN par_numero_dipendenti INT,
    IN par_logo VARCHAR(100),
    IN par_numero_bilanci INT,
    IN par_cf VARCHAR(100))
    
BEGIN 
	DECLARE ControlloCf INT;
    DECLARE ControlloEsistenza INT;
    
    SET ControlloCf = (SELECT COUNT(*)
					   FROM RESPONSABILE_AZIENDALE
                       WHERE cf = par_cf);
	IF ControlloCf = 1 THEN
    
		SET ControlloEsistenza = (SELECT COUNT(*)
								  FROM AZIENDA
                                  WHERE nome = par_nome);
                                  
		IF ControlloEsistenza = 0 THEN
			INSERT INTO AZIENDA(nome, ragione_sociale, partita_IVA, settore, numero_dipendenti, logo, numero_bilanci, cf)
			VALUES (par_nome, par_ragione_sociale, par_partita_IVA, par_settore, par_numero_dipendenti, par_logo, par_numero_bilanci, par_cf);
			SELECT 'Autorizzato: Azienda registrata con successo' AS Esito;
        
		ELSE
			SELECT 'Errore: Esiste gia un azienda con questo nome' AS Esito;
		
        END IF;
	
    ELSE
		SELECT 'Non Autorizzato: Solo un responsabile aziendale puo registrare un azienda' AS Esito;
	
    END IF;
END$
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE PROCEDURE CreazioneBilancio(
	IN par_id INT, 
	IN par_data_creazione DATE, 
	IN par_stato VARCHAR(100), 
	IN par_partita_iva VARCHAR(100),
    IN par_cf VARCHAR(100))

BEGIN
	DECLARE ControlloCf INT;
    DECLARE ControlloEsistenzaAzienda INT;
    DECLARE ControlloEsistenzaBilancio INT;

	SET ControlloCf = (SELECT COUNT(*)
						   FROM RESPONSABILE_AZIENDALE
                           WHERE cf = par_cf);
                           
	IF ControlloCf = 1 THEN
    
        SET	ControlloEsistenzaAzienda = (SELECT COUNT(*)
											 FROM AZIENDA
                                             WHERE partita_iva = par_partita_iva);
		
        IF ControlloEsistenzaAzienda = 1 THEN
			
            -- Questo controllo è in piu, tecnicamente con l'auto increment ci pensa sql a fare da gestore dell'id
            SET ControlloEsistenzaBilancio = (SELECT COUNT(*)
											  FROM BILANCIO_DI_ESERCIZIO
                                              WHERE id = par_id);
                                              
			IF ControlloEsistenzaBilancio = 0 THEN
				INSERT INTO BILANCIO_DI_ESERCIZIO(id ,data_creazione, stato, partita_iva)
				VALUES (par_id ,par_data_creazione, par_stato, par_partita_iva);
                SELECT 'Autorizzato: Creato nuovo bilancio di esercizio' AS Esito;
			
            ELSE
				SELECT 'Errore: Esiste gia un bilancio con questo id' AS Esito;
			
            END IF;
		
        ELSE
			SELECT 'Errore: Non esiste nessuna azienda con questa partita iva' AS Esito;
		
        END IF;
        
	ELSE
		SELECT 'Non Autorizzato: Solo i responsabili aziendali possono creare i bilanci' AS Esito;
	
    END IF;
END$
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE PROCEDURE PopolamentoBilancio(
	IN par_nome_voce_contabile VARCHAR(100), 
    IN par_id_bilancio INT, 
    IN par_valore_numerico INT,
    IN par_cf VARCHAR(100))

BEGIN
	DECLARE ControlloCf INT;
    DECLARE ControlloEsistenzaBilancio INT;
    
    SET ControlloCf = (SELECT COUNT(*)
					   FROM RESPONSABILE_AZIENDALE
                       WHERE cf = par_cf);
	
    IF ControlloCf = 1 THEN
		
        SET ControlloEsistenzaBilancio = (SELECT COUNT(*)
										  FROM BILANCIO_DI_ESERCIZIO
                                          WHERE id = par_id_bilancio);
		
        IF ControlloEsistenzaBilancio = 1 THEN
			INSERT INTO COMPOSIZIONE_BILANCIO(nome_voce_contabile, id_bilancio, valore_numerico)
            VALUES (par_nome_voce_contabile, par_id_bilancio, par_valore_numerico);
            SELECT 'Autorizzato: Dati inseriti nel bilancio di esercizio' AS Esito;
            
		ELSE
			SELECT 'Errore: Non esiste nessun bilancio con questo id'  AS Esito;
		
        END IF;
	
    ELSE
		SELECT 'Non Autorizzato: Solo un responsabile aziendale può inserire dati nel bilancio di esercizio'  AS Esito;
	
    END IF;
END$
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE PROCEDURE AssociamentoIndicatoriEBilanci(
	IN par_id_bilancio INT , 
    IN par_nome_voce_contabile VARCHAR(100), 
    IN par_nome_indicatore VARCHAR(100), 
    IN par_valore_indicatore INT , 
    IN par_fonte VARCHAR(100), 
    IN par_data_rilevazione DATE,
    IN par_cf VARCHAR(100))
    
BEGIN 
	DECLARE ControlloCf INT;
    DECLARE ControlloEsistenzaIndicatore INT;
    DECLARE ControlloEsistenzaBilancio INT;
    
    SET ControlloCf = (SELECT COUNT(*)
					   FROM RESPONSABILE_AZIENDALE
                       WHERE cf = par_cf);
                       
	IF ControlloCf = 1 THEN
    
		SET ControlloEsistenzaBilancio = (SELECT COUNT(*)
										  FROM BILANCIO_DI_ESERCIZIO
                                          WHERE id = par_id_bilancio);
		
        IF ControlloEsistenzaBilancio = 1 THEN
    
			SET ControlloEsistenzaIndicatore = (SELECT COUNT(*)
												FROM INDICATORE_ESG
												WHERE nome = par_nome_indicatore);
		
			IF ControlloEsistenzaIndicatore = 1 THEN
				INSERT INTO ASSOCIAZIONE_INDICATORE_ESG(id_bilancio, nome_voce_contabile, nome_indicatore, valore_indicatore, fonte, data_rilevazione)
                VALUES (par_id_bilancio, par_nome_voce_contabile, par_nome_indicatore, par_valore_indicatore, par_fonte, par_data_rilevazione);
                SELECT 'Autorizzato: Associato correttamente l indicatore esg per questa voce di bilancio' AS Esito;
			
            ELSE
				SELECT 'Errore: Non esiste nessun indicatore con questo nome' AS Esito;
			
            END IF;
		
        ELSE
			SELECT 'Errore: Non esiste nessun bilancio con questo id' AS Esito;
            
		END IF;
	
    ELSE
		SELECT 'Non Autorizzato: Solo un responsabile aziendale puo associare un indicatore esg ad un bilancio' AS Esito;
	
    END IF;
END$
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
																						-- TRIGGER
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE TRIGGER CambioStatoBilancio
AFTER INSERT ON GIUDIZIO
FOR EACH ROW
BEGIN 
	UPDATE BILANCIO_DI_ESERCIZIO
    SET stato = 'in revisione'
    WHERE id = NEW.id_bilancio;
END$
DELIMITER ;
-- --------------------------------------------------------------------------------------------------
DELIMITER $
CREATE TRIGGER ValutazioneFinaleBilancio
AFTER UPDATE ON GIUDIZIO
FOR EACH ROW 
BEGIN 
    DECLARE TotaleRevisori INT;
    DECLARE RevisoriCheHannoVotato INT;
    DECLARE NumeroRespingimenti INT;
    
    SET TotaleRevisori = (SELECT COUNT(*)
                          FROM GIUDIZIO
                          WHERE id_bilancio = NEW.id_bilancio);
    
    SET RevisoriCheHannoVotato = (SELECT COUNT(*)
                                  FROM GIUDIZIO
                                  WHERE id_bilancio = NEW.id_bilancio 
                                  AND esito IS NOT NULL);
    
    SET NumeroRespingimenti = (SELECT COUNT(*)
                               FROM GIUDIZIO
                               WHERE id_bilancio = NEW.id_bilancio 
                               AND esito = 'respingimento');
                               
    IF RevisoriCheHannoVotato = TotaleRevisori AND NumeroRespingimenti = 0 THEN
        UPDATE BILANCIO_DI_ESERCIZIO
        SET stato = 'approvato'
        WHERE id = NEW.id_bilancio;
    
    ELSEIF RevisoriCheHannoVotato = TotaleRevisori AND NumeroRespingimenti >= 1 THEN
        UPDATE BILANCIO_DI_ESERCIZIO
        SET stato = 'respinto'
        WHERE id = NEW.id_bilancio;
    
    END IF;

END $
DELIMITER ;

INSERT INTO UTENTE (username, password, cf, data_nascita, luogo_nascita, ruolo_interno, professione)
VALUES 
('luigi_buccheri', '$2y$10$zspMQMfKil.3wrf3wIX5aO6O.iaKyoU.GdTkpPierAi7UuOYoRgzC', 'RSMRA80A01H501U', '1980-01-01', 'Bentivoglio', 'amministratore', 'amministratore'),
('sofia_luna', '$2y$10$zspMQMfKil.3wrf3wIX5aO6O.iaKyoU.GdTkpPierAi7UuOYoRgzC', 'LNASFO94A41L219Z', '1994-01-01', 'Venezia', 'utente_base', 'responsabile_aziendale'),
('matteo_rocca', '$2y$10$zspMQMfKil.3wrf3wIX5aO6O.iaKyoU.GdTkpPierAi7UuOYoRgzC', 'RCCMTT88M12H501O', '1988-12-12', 'Roma', 'revisore_esg', 'revisore_esg'),
('giovanni_pelle', '$2y$10$zspMQMfKil.3wrf3wIX5aO6O.iaKyoU.GdTkpPierAi7UuOYoRgzC', 'PLLGNN75R03F205T', '1975-03-03', 'Milano', 'responsabile_aziendale', 'responsabile_aziendale'),
('paola_verdi', '$2y$10$zspMQMfKil.3wrf3wIX5aO6O.iaKyoU.GdTkpPierAi7UuOYoRgzC', 'VRDPLA82S45G273U', '1982-11-05', 'Napoli', 'revisore_esg', 'revisore_esg'),
('stefano_neri', '$2y$10$zspMQMfKil.3wrf3wIX5aO6O.iaKyoU.GdTkpPierAi7UuOYoRgzC', 'NRISTF90P01B354Q', '1990-06-20', 'Genova', 'utente_base', 'responsabile_aziendale'),
('stefano_nerit', '$2y$10$zspMQMfKil.3wrf3wIX5aO6O.iaKyoU.GdTkpPierAi7UuOYoRgzC', 'NRISTF90P01B354P', '1990-06-20', 'Genova', 'utente_base', 'responsabile_aziendale');

INSERT INTO RECAPITO_EMAIL (indirizzo_email, cf)
VALUES 
('luigi.buccheri@example.it', 'RSMRA80A01H501U'),
('sofia.luna@example.it', 'LNASFO94A41L219Z'),
('matteo.rocca@example.it', 'RCCMTT88M12H501O'),
('giovanni.pelle@example.it', 'PLLGNN75R03F205T'),
('paola.verdi@example.it', 'VRDPLA82S45G273U'),
('stefano.neri@example.it', 'NRISTF90P01B354Q'),
('stefano.nerit@example.it', 'NRISTF90P01B354P');

INSERT INTO RESPONSABILE_AZIENDALE (cf, curriculum_vitae) VALUES 
('LNASFO94A41L219Z', 'cv_sofia.pdf'),
('PLLGNN75R03F205T', 'cv_giovanni.pdf'),
('NRISTF90P01B354Q', 'cv_stefano.pdf');

INSERT INTO REVISORE_ESG (cf) VALUES 
('RCCMTT88M12H501O'),
('VRDPLA82S45G273U');

INSERT INTO AMMINISTRATORE (cf)
VALUES ('RSMRA80A01H501U');

CALL CreazioneTemplate('Fatturato Lordo', 'Totale dei ricavi derivanti dalle vendite e prestazioni', 'RSMRA80A01H501U');
CALL CreazioneTemplate('Costi Materie Prime', 'Spese sostenute per l acquisto di beni destinati alla produzione', 'RSMRA80A01H501U');
CALL CreazioneTemplate('Salari e Stipendi', 'Costi relativi alla remunerazione del personale dipendente', 'RSMRA80A01H501U');
CALL CreazioneTemplate('Ammortamenti Immobilizzazioni', 'Quota annuale di ripartizione del costo dei beni strumentali', 'RSMRA80A01H501U');
CALL CreazioneTemplate('Oneri Finanziari', 'Interessi passivi e altri costi derivanti da finanziamenti', 'RSMRA80A01H501U');

CALL PopolamentoIndEsg('Emissioni CO2 Scope 1', 'co2_icon.png', 10, 'RSMRA80A01H501U', 'ambientali', 'ISO 14064', NULL, NULL);
CALL PopolamentoIndEsg('Consumo Idrico Totale', 'water_drop.png', 8, 'RSMRA80A01H501U', 'ambientali', 'GRI 303', NULL, NULL);
CALL PopolamentoIndEsg('Efficienza Energetica', 'energy_bolt.png', 9, 'RSMRA80A01H501U', 'ambientali', 'Direttiva UE 2012/27', NULL, NULL);
CALL PopolamentoIndEsg('Gender Pay Gap', 'equality.png', 9, 'RSMRA80A01H501U', 'sociali', NULL, 'Parità di Genere', 'Annuale');
CALL PopolamentoIndEsg('Ore Formazione Sicurezza', 'safety_helmet.png', 7, 'RSMRA80A01H501U', 'sociali', NULL, 'Salute e Sicurezza', 'Semestrale');
CALL PopolamentoIndEsg('Turnover del Personale', 'people_flow.png', 6, 'RSMRA80A01H501U', 'sociali', NULL, 'Risorse Umane', 'Annuale');

INSERT INTO AZIENDA (nome, ragione_sociale, partita_IVA, settore, numero_dipendenti, logo, numero_bilanci, cf)
VALUES 
('EcoTech Solutions', 'S.p.A.', 'IT12345678901', 'Tecnologia', 150, 'ecotech_logo.png', 0, 'PLLGNN75R03F205T'),
('Green Future', 'S.r.l.', 'IT09876543210', 'Energia Rinnovabile', 45, 'greenfuture_logo.png', 0, 'LNASFO94A41L219Z');

INSERT INTO BILANCIO_DI_ESERCIZIO (id, data_creazione, stato, partita_iva)
VALUES 
(1, '2025-12-31', 'approvato', 'IT12345678901'),
(2, '2026-05-08', 'bozza', 'IT09876543210');

-- Valori per il bilancio 100 (EcoTech Solutions)
INSERT INTO COMPOSIZIONE_BILANCIO (id_bilancio, nome_voce_contabile, valore_numerico)
VALUES 
(1, 'Fatturato Lordo', 2500000),
(1, 'Costi Materie Prime', 800000),
(1, 'Salari e Stipendi', 600000),
(1, 'Ammortamenti Immobilizzazioni', 150000),
(1, 'Oneri Finanziari', 45000);

-- Valori per il bilancio 101 (Green Future)
INSERT INTO COMPOSIZIONE_BILANCIO (id_bilancio, nome_voce_contabile, valore_numerico)
VALUES 
(2, 'Fatturato Lordo', 850000),
(2, 'Salari e Stipendi', 320000),
(2, 'Oneri Finanziari', 12000);