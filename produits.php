<?php 
require_once "db/db.php";
include 'components/header.php';
include 'db/db_implement.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produits</title>
    <link rel="stylesheet" href="/Technova/CSS/global.css">
    <link rel="stylesheet" href="/Technova/CSS/pageProd.css">
    <script src="/Technova/JS/priceFilter.js"></script>
</head>

<body>
    <h2> Nos produits</h2>   
    <section class="produits">
 
            <div class="panel">
                <div class="filtre-prix">
                    <label style="font-size: 16px; text-align: center;">Filtrer par prix</label>
                    <input type="range" min="0" max="1000" step="10" value="0" id="price-slider" onchange="changePrice(this)">
                    <div id="price-range" style="font-size: 16px; text-align: center;">0 € - 1000 €</div>
                    <button id="apply-filter">Appliquer le filtre</button>
                </div>
            </div>
        <div class="container">
            <?php include 'db/db_productsGrid.php'; ?>
        </div>
    </section>

    
</body>
    <?php require 'components/footer.php'; ?>
</html>