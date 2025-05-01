#ifndef UNIVERSITE_H
#define UNIVERSITE_H
#include<string>
#include<iostream>
#include<vector>
using namespace std;

class universite
{
    public:
        universite();
        vector<enseignant*>::iterator rechercherEnseignant(int id);
        vector<classe>::iterator  rechercherClasse(string id);
        void ajouter(const enseignant& e);
        void ajouter(const expert& a);
        universite(string nom_univ);
        ~universite();
        universite(const universite& other);

        string Getnom_univ() { return nom_univ; }
        void Setnom_univ(string val) { nom_univ = val; }

    protected:

    private:
        string nom_univ;
        vector<enseignant*>LE;
        vector<classe>LC;
};

#endif // UNIVERSITE_H
