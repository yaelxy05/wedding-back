<?php

namespace App\DataFixtures\Provider;

class ApoWeddingProvider
{

    private $tasks = [
        "Je dois faire ma liste d'invités",
        "Je dois commander le gâteau",
        "Je dois réserver le traiteur",
        "Je dois appeler différents DJ",
        "Je dois commander des chaises",
        "Je dois commander des tables",
        "Je dois envoyer les faire parts",
        "Je dois commander les faire parts",
        "Je dois acheter ma déco de table",
        "Je dois me fixer un budget",
        "Je dois acheter des bonbons pour le candy bar",
        "Je dois choisir mes témoins",
        "Je dois aller voir différents coiffeurs",
        "Je dois aller voir différentes esthéticienne",
        "Je dois choisir mon esthéticienne",
        "Je dois choisir mon coiffeur",
        "Je dois choisir ma maquilleuse",
        "Je dois trouver la musique",
        "Trouver des animations amusantes",
        "Faciliter la venue aux invités qui viennent de loin",
        "Engager un photographe",
        "Commencer à chercher une destination pour la lune de miel",
        "Prévoir mon enterrement de vie de jeune fille",
        "Prévoir mon enterrement de vie de jeune garçon",
    ];
    
    /**
     * Returns a random task
     */
    public function userTasks()
    {
        return $this->tasks[array_rand($this->tasks)];
    }
}