<?php
    // Requête SQL pour récupérer les informations des produits
    $sql="select p.ID_PRO as 'idpro', p.Nom as 'prodnom', p.Description as 'proddesc', p.Prix as 'prodprix', m.Nom as 'marnom' from produit p left outer join marque m on p.ID_Marque=m.ID_Marque";

    // Exécution de la requête SQL
    $res=mysqli_execute_query($conn, $sql);
?>