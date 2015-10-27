<?php
namespace model;


class Cellule {
    private $joueur;

    public function __construct($joueur = null){
        $this->joueur = $joueur;
    }

    public function getJoueur(){ return $this->joueur; }
    public function setJoueur($joueur){ $this->joueur = $joueur; }
    public function toHtml(){
        if(!is_null($this->joueur))
            return $this->joueur->getPrenom();
        else
            return " ";
    }
}