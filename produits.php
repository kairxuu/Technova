<?php
require_once "db/db.php";
$extraCSS  = ['/Technova/CSS/pageProd.css'];
$pageTitle = "Nos produits — Technova";
include 'components/header.php';
include 'db/db_implement.php';
?>

<h2>Nos produits</h2>

<section class="produits">

    <div class="panel">

        <!-- Recherche texte -->
        <div class="filtre-recherche">
            <label for="search-produits">Rechercher</label>
            <div class="search-input-wrapper">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input type="text" id="search-produits" placeholder="Nom, marque...">
            </div>
        </div>

        <!-- Filtre prix -->
        <div class="filtre-prix">
            <label for="price-slider">Prix maximum</label>
            <input type="range" min="0" max="5000" step="50" value="5000" id="price-slider">
            <div id="price-range" style="text-align:center;font-size:16px;">5 000 €</div>
            <button id="apply-filter">Appliquer le filtre</button>
        </div>

    </div>

    <div class="container">
        <?php include 'db/db_productsGrid.php'; ?>
    </div>

</section>

<!-- Script inline : pas de defer, s'exécute après le HTML -->
<script>
// --- Mise à jour de l'affichage du prix sous le slider ---
document.getElementById('price-slider').addEventListener('input', function() {
    var val = parseInt(this.value);
    document.getElementById('price-range').textContent = val.toLocaleString('fr-FR') + ' €';
});

// --- Filtre au clic sur "Appliquer le filtre" ---
document.getElementById('apply-filter').addEventListener('click', function() {
    var prixMax = parseInt(document.getElementById('price-slider').value);
    var texte   = document.getElementById('search-produits').value.toLowerCase().trim();

    document.querySelectorAll('.product-card').forEach(function(carte) {
        var prix   = parseFloat(carte.getAttribute('data-price')) || 0;
        var nom    = (carte.querySelector('h3') || {}).textContent || '';
        var marque = (carte.querySelector('.product-brand') || {}).textContent || '';

        var okPrix   = prix <= prixMax;
        var okTexte  = texte === '' || nom.toLowerCase().includes(texte) || marque.toLowerCase().includes(texte);

        carte.style.display = (okPrix && okTexte) ? '' : 'none';
    });
});

// --- Filtre texte en temps réel ---
document.getElementById('search-produits').addEventListener('input', function() {
    // Simule un clic sur le bouton pour appliquer les deux filtres ensemble
    document.getElementById('apply-filter').click();
});
</script>

<?php require 'components/footer.php'; ?>