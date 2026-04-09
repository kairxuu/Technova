<?php
// Requête SQL pour récupérer les produits
$sql = "SELECT 
            p.ID_PRO as 'idpro', 
            p.Nom as 'prodnom', 
            p.Description as 'proddesc', 
            p.Prix as 'prodprix',
            p.image as 'image', 
            m.Nom as 'marnom' 
        FROM 
            produit p 
        LEFT JOIN 
            marque m ON p.ID_Marque = m.ID_Marque";

$res = mysqli_execute_query($conn, $sql);