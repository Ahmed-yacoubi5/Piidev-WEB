#include "classe.h"

classe::classe(string designation)
{
    this->designation=designation;
}
classe::classe()
{
    designation="";
}
classe::~classe
{
    //dtor
}
void classe::afficher()
{
    cout<<"designation: "<<designation <<endl;
}
int classe::verifierEnseigMat(int matricule){

    for(int i=0;i<EnsMatricule.size();i++)
    {
        if(EnsMatricule[i]==matricule)return i;
    }
    return -1;

}
bool classe::ajouterMatricule(int matricule)
{
    if(verifierEnseigMat(matricule)==-1)
    {EnsMatricule.push_back(matricule);
        return true;
    }
    return false;
}
