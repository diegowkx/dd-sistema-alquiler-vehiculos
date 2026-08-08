-- Validación de NOT NULL y NULL para los sistemas.
DELIMITER //

CREATE TRIGGER before_insert_comprobante
BEFORE INSERT ON comprobantes_pago
FOR EACH ROW
BEGIN
    IF NOT ((NEW.tipo_origen = 'RESERVA' AND NEW.reserva_id IS NOT NULL AND NEW.estadia_id IS NULL) OR 
            (NEW.tipo_origen = 'ESTADIA' AND NEW.estadia_id IS NOT NULL AND NEW.reserva_id IS NULL)) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error de validación: El origen no coincide con las IDs proporcionadas.';
    END IF;
END;
//

CREATE TRIGGER before_update_comprobante
BEFORE UPDATE ON comprobantes_pago
FOR EACH ROW
BEGIN
    IF NOT ((NEW.tipo_origen = 'RESERVA' AND NEW.reserva_id IS NOT NULL AND NEW.estadia_id IS NULL) OR 
            (NEW.tipo_origen = 'ESTADIA' AND NEW.estadia_id IS NOT NULL AND NEW.reserva_id IS NULL)) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error de validación: El origen no coincide con las IDs proporcionadas.';
    END IF;
END;
//

DELIMITER ;