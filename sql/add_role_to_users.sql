-- Ajout de la colonne role si elle n'existe pas encore (SQLite)
ALTER TABLE users ADD COLUMN role TEXT NOT NULL DEFAULT 'member' CHECK (role IN ('admin', 'creator', 'member'));

-- Migration des anciennes valeurs eventuelles
UPDATE users
SET role = CASE
    WHEN lower(role) IN ('admin') THEN 'admin'
    WHEN lower(role) IN ('creator') THEN 'creator'
    ELSE 'member'
END;
