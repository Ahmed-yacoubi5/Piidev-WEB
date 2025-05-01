#ifndef ENSEIGNANT_H
#define ENSEIGNANT_H
#include<string>
#include<iostream>
using namespace std;

class enseignant
{
    public:
        enseignant();
        virtual void afficher();
        enseignant(int matricule,string nom,string prenom,int nbre_h);
        virtual ~enseignant();

        int Getmatricule() { return matricule; }
        void Setmatricule(int val) { matricule = val; }
        string Getnom() { return nom; }
        void Setnom(string val) { nom = val; }
        string Getprenom() { return prenom; }
        void Setprenom(string val) { prenom = val; }
        int Getnbre_h() { return nbre_h; }
        void Setnbre_h(int val) { nbre_h = val; }

    protected:
        int matricule;
        string nom;
        string prenom;
        int nbre_h;
};

#endif // ENSEIGNANT_H
