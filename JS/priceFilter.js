/**
 * FICHIER : priceFilter.js
 * RÔLE    : Filtrer les cartes produits par prix et par texte de recherche
 *
 * ARCHITECTURE :
 * - updatePrixDisplay()  → appelée à chaque mouvement du slider (oninput)
 *                          met juste à jour le texte et la couleur du slider
 * - appliquerFiltre()    → appelée au clic sur "Appliquer le filtre"
 *                          déclenche le vrai filtrage des cartes
 * - rechercherProduit()  → appelée à chaque lettre tapée dans la recherche (oninput)
 *                          déclenche aussi le filtrage immédiatement
 * - filtrerProduits()    → cœur du filtre, compare prix ET texte sur chaque carte
 */


/* ============================================================
   1. MISE À JOUR DE L'AFFICHAGE DU SLIDER (sans filtrer)
   ============================================================ */

/**
 * updatePrixDisplay(input)
 * Appelée à chaque mouvement du slider (oninput dans le HTML).
 * Ne filtre PAS — met seulement à jour le texte et la couleur.
 */
function updatePrixDisplay(input) {
    const prixMax = parseInt(input.value);

    // Met à jour le texte sous le slider, ex : "0 € - 1 500 €"
    const zone = document.getElementById('price-range');
    if (zone) {
        zone.textContent = '0 € - ' + prixMax.toLocaleString('fr-FR') + ' €';
    }

    // Met à jour la couleur de la barre du slider (partie gauche en violet)
    const pct = ((prixMax - input.min) / (input.max - input.min)) * 100;
    input.style.setProperty('--pct', pct + '%');
}


/* ============================================================
   2. APPLIQUER LE FILTRE (bouton "Appliquer le filtre")
   ============================================================ */

/**
 * appliquerFiltre()
 * Appelée au clic sur le bouton "Appliquer le filtre".
 * Lit le slider, puis lance filtrerProduits().
 */
function appliquerFiltre() {
    const slider = document.getElementById('price-slider');
    const prixMax = slider ? parseInt(slider.value) : 5000;
    filtrerProduits(prixMax);
}


/* ============================================================
   3. RECHERCHE TEXTE EN TEMPS RÉEL
   ============================================================ */

/**
 * rechercherProduit(texte)
 * Appelée à chaque lettre tapée dans le champ de recherche (oninput).
 * Relit le slider et relance filtrerProduits() immédiatement.
 */
function rechercherProduit(texte) {
    const slider = document.getElementById('price-slider');
    const prixMax = slider ? parseInt(slider.value) : 5000;
    filtrerProduits(prixMax);
}


/* ============================================================
   4. CŒUR DU FILTRE : compare chaque carte
   ============================================================ */

/**
 * filtrerProduits(prixMax)
 * Parcourt toutes les cartes produits et les affiche ou cache
 * selon deux critères CUMULATIFS :
 *   - prix du produit <= prixMax choisi sur le slider
 *   - nom ou marque du produit contient le texte de recherche
 */
function filtrerProduits(prixMax) {

    // Récupère toutes les cartes produits
    const cartes = document.querySelectorAll('.product-card');

    // Récupère le texte de recherche (en minuscules pour ignorer la casse)
    const champ = document.getElementById('search-produits');
    const texte = champ ? champ.value.toLowerCase().trim() : '';

    let nombreVisible = 0;

    cartes.forEach(function(carte) {

        // Lit le prix depuis l'attribut data-price mis par PHP
        // ex: <div class="product-card" data-price="1299.99">
        const prixProduit = parseFloat(carte.dataset.price) || 0;

        // Lit le nom depuis le <h3> de la carte
        const nomEl   = carte.querySelector('h3');
        const nom     = nomEl ? nomEl.textContent.toLowerCase() : '';

        // Lit la marque depuis .product-brand (peut ne pas exister)
        const marqueEl = carte.querySelector('.product-brand');
        const marque   = marqueEl ? marqueEl.textContent.toLowerCase() : '';

        // Condition 1 : le prix est dans le budget
        const okPrix = prixProduit <= prixMax;

        // Condition 2 : le texte correspond au nom OU à la marque
        //               (si le champ est vide, tous les produits passent)
        const okTexte = texte === '' || nom.includes(texte) || marque.includes(texte);

        if (okPrix && okTexte) {
            carte.style.display = '';    // Affiche la carte
            nombreVisible++;
        } else {
            carte.style.display = 'none'; // Cache la carte
        }
    });

    // Affiche un message si aucune carte n'est visible
    gererMessageVide(nombreVisible, cartes.length);
}


/* ============================================================
   5. MESSAGE "AUCUN RÉSULTAT"
   ============================================================ */

/**
 * gererMessageVide(nombreVisible, total)
 * Affiche ou cache un message quand aucun produit ne correspond.
 */
function gererMessageVide(nombreVisible, total) {
    let msg = document.getElementById('filtre-vide');

    if (nombreVisible === 0 && total > 0) {
        if (!msg) {
            msg = document.createElement('div');
            msg.id = 'filtre-vide';
            msg.style.cssText = 'grid-column:1/-1;text-align:center;padding:40px;color:var(--text-secondary);font-size:1rem;';
            msg.innerHTML = '😕 Aucun produit ne correspond à ces critères.';
            const grille = document.querySelector('.products-grid');
            if (grille) grille.appendChild(msg);
        }
        msg.style.display = '';
    } else if (msg) {
        msg.style.display = 'none';
    }
}


/* ============================================================
   6. INITIALISATION au chargement de la page
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    const slider = document.getElementById('price-slider');
    if (slider) {
        // Initialise la couleur et le texte du slider au chargement
        updatePrixDisplay(slider);
    }
});