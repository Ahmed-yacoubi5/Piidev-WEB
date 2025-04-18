#include "enseignant.h"

enseignant::enseignant(int matricule,string nom,string prenom,int nbre_h)
{
       this->matricule= matricule;
   this->nom= nom;
    this->prenom=prenom;
    this->nbre_h=nbre_h;
}
enseignant::enseignant()
{nom= "";
   prenom= "";
    matricule= 0;
    nbre_h=0;}
enseignant::~enseignant()
{
    //dtor
}
void enseignant::afficher()
{
 cout<<"Matricule : "<<matricule<<endl<<"Nom: "<<nomE<<endl<<"prenom: "<<prenomE<<endl<<"le nombre des heures assurees.: "<<nbreHeure<<endl;
}
