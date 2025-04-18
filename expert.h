#ifndef EXPERT_H
#define EXPERT_H

#include <enseignant.h>


class expert : public enseignant
{
    public:
        expert();
        void afficher();
        expert(int matricule,string nom,string prenom,int nbre_h,string nom_entreprise);
        ~expert();

        string Getnom_entreprise() { return nom_entreprise; }
        void Setnom_entreprise(string val) { nom_entreprise = val; }

    protected:

    private:
        string nom_entreprise;
};

#endif // EXPERT_H
