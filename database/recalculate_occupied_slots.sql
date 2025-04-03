DELIMITER $$

CREATE PROCEDURE RecalculateOccupiedSlots()
BEGIN
    UPDATE rooms r
    SET r.occupied_slots = (
        SELECT COUNT(*) 
        FROM students s 
        WHERE s.room_id = r.room_id
    );
END$$

DELIMITER ;
