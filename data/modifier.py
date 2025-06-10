import csv
import os


with open('data.csv', 'r', encoding='utf-8') as infile, \
        open('data_clean.csv', 'w', encoding='utf-8', newline='') as outfile:
    lecteur = csv.reader(infile)
    ecrivain = csv.writer(outfile)

    for ligne in lecteur:
        ligne_modifiee = [cellule.replace("Normandy", "Normandie") for cellule in ligne]
        ecrivain.writerow(ligne_modifiee)
