# One-Click Apps

Par défaut, Towerify Cloud dispose d'un catalogue intégré d'applications populaires prêtes à l'emploi. Déployer et
configurer ces applications avec Towerify Cloud est aussi simple que d'installer une application sur votre téléphone!

Parce que maintenir votre système à jour et sécurisé est un travail à plein temps, Towerify Cloud s'occupe de mettre à
jour automatiquement ces applications. **Towerify Cloud vous permet ainsi de vous concentrer sur l'utilisation de ces
applications** et de ne pas avoir à vous préoccuper de l'administration du système.

## Catalogue

<div id="gallery"></div>
<style>
    .card {
        border: 2px solid #00264b;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        margin-bottom: 15px;
    }
    .card .card-head {
        display: flex;
        align-items: center;
        justify-content: left;
        padding-top: 0.5rem;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    .card .card-head img {
        height: 50px;
    }
    .card .card-head h2 {
        font-weight: bold;
        margin: 0;
        padding: 0.5rem;
    }
    .card .card-body .desc {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        padding-bottom: 0.5rem;
    }
    .card .card-head .badge {
        font-size: 0.5rem;
        background-color: #66bb70;
        color: white;
        padding: 2px 4px;
        border-radius: 5px;
        margin-left: auto;
        margin-right: 0.5rem;
    }
</style>
<script>
    /* from https://codepen.io/DivyaPatel/pen/dxjgVL */
    fetch("https://app.towerify.io/catalog")
    .then((response) => response.json())
    .then((apps) => {
        const el = document.getElementById('gallery'); 
        apps.forEach(app => {
            const card = document.createElement('div');
            card.innerHTML = `
                <div class="card">
                    <div class="card-head">
                        <img src="${app.image}">
                        <h2>${app.name}</h2>
                        <span class="badge">${app.status}</span> 
                    </div>
                    <div class="card-body">
                        <div class="desc">
                            ${app.description}
                        </div>
                    </div>
                </div>
            `;
            el.appendChild(card);
            // console.log(app);
        });
        const header = document.getElementById('applications-supportees');
        header.innerHTML = `
            Applications supportées (${apps.length})
            <a class="headerlink" href="#applications-supportees" title="Permanent link">¶</a>
        `;
    });
</script>