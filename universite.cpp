#include "universite.h"

universite::universite(string nom_univ)
{
    this->nom_univ=nom_univ;
}

universite::~universite()
{
vector<enseignant*>::iterator i;
    for(i=LE.begin();i!=LE.end();i++)
        delete(*i);
}
universite::universite()
{
    nom_univ="";
}
universite::universite(const universite& a)
{
    this->nom_univ=a.nom_univ;
    enseignant* p;
    vector<enseignant*>::const_iterator i;
    for(i=a.LE.begin();i!=a.LE.end();i++)
    {
        if(typeid(**i)==typeid(enseignant))
            p=new enseignant(**i);
        else
            if(typeid(**i)==typeid(expert))
                p=new expert(static_cast<const expert&>(**i));
    }
    LE.push_back(p);
}
vector<enseignant*>::iterator universite::rechercherEnseignant(int id)
{
    vector<enseignant*>::iterator i;
    for(i=LE.begin();i!=LE.end();i++)
        if((*i)->getMatricule()==id)
            return i;
    return LE.end();
}
vector<classe>::iterator  universite::rechercherClasse(string id)
{
    vector<classe>::iterator i;
    for(i=LC.begin();i!=LC.end();i++)
        if(i->getdesignation()==id)
            return i;
    return LC.end();
}
void universite::ajouter(const enseignant& e)
{
    if(rechercherEnseignant(e.getMatricule())==LE.end())
    {
        enseignant *p=new enseignant(e);
        LE.push_back(p);
    }
    else
        cout<<"L'enseignant(e) existe deja"<<endl;
}

void universite::ajouter(const expert& a)
{
    if(rechercherEnseignant(a.getMatricule())==LE.end())
    {
        enseignant *p=new expert(a);
        LE.push_back(p);
    }
    else
        cout<<"L'enseignant(e) expert existe deja"<<endl;
}
