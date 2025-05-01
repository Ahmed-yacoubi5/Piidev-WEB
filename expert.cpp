#include "expert.h"

expert::expert(int matricule,string nom,string prenom,int nbre_h,string nom_entreprise):enseignant::enseignant(matricule,nom,prenom,nbre_h)
{
    this-> nom_entreprise=nom_entreprise;
}
expert::expert()
{
    nom_entreprise="";
}
expert::~expert()
{

}
void expert::afficher()
{
    enseignant::afficher();
    cout<<"nom_entreprise : "<<nom_entreprise<<endl;
}
