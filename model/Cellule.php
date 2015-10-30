<?php
namespace model;


class Cellule {
    /**
     * @var Joueur $joueur Le joueur qui a le pion sur la case. Si il n'y a pas de pion sur la case, vaut null.
     */
    private $joueur;

    /**
     * @var int $x Position en X de la cellule (position horizontale). Doit être comprise entre 0 et 4.
     */
    private $x;

    /**
     * @var int $y Position en Y de la cellule (position verticale). Doit être comprise entre 0 et 4.
     */
    private $y;


    // -------------------------------------------------------------------------------------------------


    /**
     * Constucteur de la cellule, qui va l'initialiser avec sa position.
     * @param int $x Position en X de la cellule.
     * @param int $y Position en Y de la cellule.
     */
    public function __construct($x, $y){
        $this->x = $x;
        $this->y = $y;
    }


    /**
     * @return int Position en X (Horizontale) de la cellule. Comprise entre 0 et 4.
     */
    public function getX(){
        return $this->x;
    }


    /**
     * @return int Position en Y (Verticale) de la cellule. Comprise entre 0 et 4.
     */
    public function getY(){
        return $this->y;
    }


    /**
     * @return Joueur Retourne le joueur à qui appartient le pion dans la cellule. Null si la cellule est vide.
     */
    public function getJoueur(){
        return $this->joueur;
    }


    /**
     * @param Joueur $joueur Joueur à qui le pion sur la cellule appartiendra. Null pour vider la cellule.
     */
    public function setJoueur($joueur){
        $this->joueur = $joueur;
    }


    /**
     * Méthode pour savoir quelles cellules sont accolées à la cellule courante.
     * @param string $direction Chaîne de caractère contenant la direction de la case suivante voulue parmi les valeurs suivantes (points cardinaux) : "n", "ne", "e", "se", "s", "so", "o", "no".
     * @return null | Cellule Retourne la cellule suivante dans la direction demandé. Null si la cellule est en bord de plateau.
     */
    public function getCelluleSuivante($direction){
        if(isset($_SESSION["partie"])){
            $plateau = unserialize($_SESSION["partie"])->getPlateau(); // On récupère le plateau dans la session.
            switch ($direction){ // Selon la direction demandé on retourne la case voulue.
                case "no":
                    return $plateau->getCellule($this->x - 1, $this->y - 1);
                    break;
                case "n" :
                    return $plateau->getCellule($this->x, $this->y - 1);
                    break;
                case "ne" :
                    return $plateau->getCellule($this->x + 1, $this->y - 1);
                    break;
                case "e" :
                    return $plateau->getCellule($this->x + 1, $this->y);
                    break;
                case "se" :
                    return $plateau->getCellule($this->x + 1, $this->y + 1);
                    break;
                case "s" :
                    return $plateau->getCellule($this->x, $this->y + 1);
                    break;
                case "so" :
                    return $plateau->getCellule($this->x - 1, $this->y + 1);
                    break;
                case "o" :
                    return $plateau->getCellule($this->x - 1, $this->y);
                    break;
                default:
                    return null; // Si la direction demandée est incorrect, on retourne rien.
                    break;
            }
        } else{
            return null;
        }
    }


    /**
     * Détermine si une cellule est isolée, c'est à dire qu'elle n'a aucun pion voisin.
     * @return bool Vrai si elle est isolée
     */
    public function isolee(){
        $cellulesVoisines = $this->cellulesVoisines();
        if(empty($cellulesVoisines)) // Au cas où le tableau est vide
            return false;
        else{
            foreach($cellulesVoisines as $celluleVoisine){
                if($celluleVoisine->getJoueur() != null){ // Si le voisin n'est pas null (cellule vide) et n'a pas le même joueur (donc adverse), on a au moins un pion adverse.
                    return false;
                }
            }
            return true;
        }
    }


    /**
     * Retourne le tableau des cellules qui entoure la cellule en question. Les cellules hors-plateau ne sont pas comptabilisées.
     * @return array Tableau des cellules voisines de la cellule courante.
     */
    private function cellulesVoisines(){
        $directions = ["n", "ne", "e", "se", "s", "so", "o", "no"]; //Toutes les directions possibles.
        $voisins = [];
        foreach($directions as $direction){
            if(!is_null($this->getCelluleSuivante($direction))) // Si la cellule suivante est hors plateau, elle n'est pas prise en compte.
                array_push($voisins, $this->getCelluleSuivante($direction));
        }

        return $voisins;
    }


    /**
     * Méthode afin de récupérer toutes les cellules sur lesquelles pourrait se déplacer le pion courant.
     * @return array Tableau des cellules où le déplacement est possible.
     */
    public function getCellulesSuivantesDisponibles(){
        $cellulesSuivantesDisponibles = [];

        // On ajoute les cases suivantes dans chaque direction en utilisant la méthode privée.
        $directions = ["n", "ne", "e", "se", "s", "so", "o", "no"]; //Toutes les directions possibles.
        foreach($directions as $direction) {
            $cellulesSuivantesDisponibles = array_merge($cellulesSuivantesDisponibles, $this->getCelluleSuivanteDisponible($direction, []));
        }

        // On ne tient pas compte dans le cas ou la case suivante est cette propre case.
        foreach($cellulesSuivantesDisponibles as $key => $celluleSuivante){
            if($celluleSuivante == $this)
                unset($cellulesSuivantesDisponibles[$key]);
        }

        return $cellulesSuivantesDisponibles;
    }


    /**
     * Méthode privée récursive utilisée pour déterminer toutes les cellules où le déplacement est possible dans une direction donnée.
     * @param string $direction Direction où regarder les déplacements possible. Les valeurs sont les points cardinaux suivants : "n", "ne", "e", "se", "s", "so", "o", "no".
     * @param array $cellulesPrecedantes La méthode étant récursive, on récupère les cellules cibles disponibles trouvées précedements pour les ajouter à celles qui suivent.
     * @return array Retourne un tableau de Cellules contenant les cellules où le déplacement est possible.
     */
    private function getCelluleSuivanteDisponible($direction, array $cellulesPrecedantes){
        $celluleSuivante = $this->getCelluleSuivante($direction);
        // Si le déplacement dans la direction n'est plus possible (On arrive en bord, donc on a une cellule à null, ou on arrive sur une cellule suivante appartenant à un joueur et ayant un pion).
        if(is_null($celluleSuivante) || $celluleSuivante->getJoueur() != null){
            array_push($cellulesPrecedantes, $this); // On ajoute cette cellule aux cellules possibles et on retourne le tout.
            return $cellulesPrecedantes;
        }
        // le déplacement est encore possible sur la cellule suivante
        else {
            array_push($cellulesPrecedantes, $this); // On ajoute cette cellule aux cellules possibles.
            return $celluleSuivante->getCelluleSuivanteDisponible($direction, $cellulesPrecedantes); // On continu récursivement sur la cellule suivante.
        }
    }


    /**
     * Méthode qui permet de déterminer si oui ou non on peut déplacer le pion sur la cellule courante.
     * @return bool Vrai si la cellule est déplacable, faux sinon.
     */
    public function deplacable(){
        $partie = Partie::charger();

        // Si la case n'appartient à aucun joueur (il n'y a pas de pion dessus), on ne peut la déplacer.
        if($this->joueur == null)
            return false;

        // Si on a aucune case disponible pour déplacer le pion, on ne le déplace pas
        if(empty($this->getCellulesSuivantesDisponibles()))
            return false;

        // On vérifie que si il y a des pions isolés, ce pion peut rompre l'isolement
//        $peutRompreIsolement = false;
//        $pionsIsoles = $partie->pionsIsoles($this->joueur);
//        if(!empty($pionsIsoles)){ // Si on a au moins un pion isolé
//            foreach($pionsIsoles as $pionIsole){
//                if(in_array($pionIsole, $this->getCellulesSuivantesDisponibles())){
//                    $peutRompreIsolement = true;
//                }
//            }
//        } else{ // Si il n'y a pas de pion isolé, on met vrai car tout est ok.
//            $peutRompreIsolement = true;
//        }
//
//        // Si le pion ne peut pas rompre un isolement, il n'est pas déplacable.
//        if(!$peutRompreIsolement)
//            return false;

        // On vérifie que le pion a au moins un poin voisin du même joueur pour pouvoir être déplacé.
        for($x = -1; $x <= 1; $x++){
            for($y = -1; $y <= 1; $y++){
                $cellule = $partie->getPlateau()->getCellule($this->x + $x, $this->y + $y);
                if($cellule != $this && $cellule != null && $cellule->getJoueur() == $this->joueur) // On ne prend pas en compte si la cellule examiné est cette cellule (x et y = 0) ou si elle est hors plateau (null).
                    return true;
            }
        }

        return false;
    }


    /**
     * @return string Retourne la cellule en HTML pour pouvoir l'afficher. Selon l'état de la cellule (déplacable ou non, étape 1 ou 2...), on n'utilisera pas la même balise.
     */
    public function toHtml(){
        $partie = unserialize($_SESSION["partie"]);
        $etape = $partie->getEtape(); // L'affichage dépend de l'étape
        $joueurCourant = $partie->getJoueurCourant();

        // Si il n'agit d'une cellule vide.
        if(is_null($this->joueur)){
            // Si l'étape est la 1, on a rien à afficher, si c'est la 2, on affiche les cases où le déplacement est possible. À chaque cellule ou le déplacement est possible on met une url avec les informations nécessaires pour le traitement : etape, x, y.
            if($etape == 2 && in_array($this, $partie->getCelluleADeplacer()->getCellulesSuivantesDisponibles()))
                return "<a class='pion possible' href='?etape=2&x=".$this->x."&y=".$this->y."' style='background-color:".$joueurCourant->getCouleur()."'></a>";
            else
                return "";
        }

        // Si il y a un pion sur la cellule.
        else{
            // Si on est à l'étape 1 et que le pion est déplacable et qu'il appartient au joueur à qui s'est le tour, le pion est cliquable, et on fait un lien avec les informations nécessaires (étape, x, y).
            if($etape == 1 && $this->deplacable() && $partie->getJoueurCourant() == $this->joueur)
                return "<a class='pion' href='?etape=".$etape."&x=".$this->x."&y=".$this->y."' style='background-color:".$this->joueur->getCouleur()."'></a>";
            // Si on est à l'étape 2, et que la cellule en question est la cellule choisie pour être déplacée à l'étape précedente, on l'affiche en plus grosse (avec la class déplacement en css).
            else if($etape == 2 && $this == $partie->getCelluleADeplacer())
                return "<div class='pion deplacement' style='background-color:".$this->joueur->getCouleur()."'></a>";
            // Si le pion n'est pas déplacable et qu'il n'a pas été choisi à l'étape précedente, on l'affiche normalement.
            else
                return "<div class='pion' style='background-color:".$this->joueur->getCouleur()."'></div>";
        }
    }
}