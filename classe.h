#ifndef CLASSE_H
#define CLASSE_H
#include<string>
#include<iostream>
#include<vector>
using namespace std;

class classe
{
    public:
        classe();
        int verifierEnseigMat(int matricule);
        bool ajouterMatricule(int matricule);
        void afficher();
        classe(string designation);
        ~classe();

        string Getdesignation() { return designation; }
        void Setdesignation(string val) { designation = val; }

    protected:

    private:
        string designation;
        vector<int>EnsMatricule;
};

#endif // CLASSE_H
