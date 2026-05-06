<?php
// --- REQUÊTE PRODUITS ---
// Récupère tous les produits avec leur marque depuis la base de données.
// Le résultat est stocké dans $res, utilisé ensuite par les pages pour afficher les produits.

require_once __DIR__ . '/db.php'; // Garantit que $conn est disponible

// Requête SQL : sélectionne les colonnes utiles en les renommant pour simplifier leur accès ensuite
$sql = "SELECT 
            p.ID_PRO      as 'idpro',    -- ID du produit
            p.Nom         as 'prodnom',  -- Nom du produit
            p.Description as 'proddesc', -- Description
            p.Prix        as 'prodprix', -- Prix
            p.image       as 'image',    -- Nom du fichier image
            m.Nom         as 'marnom'    -- Nom de la marque (via jointure)
        FROM 
            produit p 
        LEFT JOIN 
            marque m ON p.ID_Marque = m.ID_Marque"; // Jointure pour récupérer la marque associée

// Exécute la requête et stocke le résultat dans $res
$res = mysqli_execute_query($conn, $sql);