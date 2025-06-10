def ligne_correcte(ligne):
    char_corrects = """*=€[]ä°@ü#!ÿ%ö&?œëûàêïâîç+éèùô0123456789abcdefghijklmnopqrstuvwxyz,/:-."_ ()'\n"""
    for c in ligne:
        if c.lower() not in char_corrects:
            print(c)
            return False
    return True

csv = open('data.csv', 'r', encoding='utf-8')
count = 0
for numero, ligne in enumerate(csv, start=1):
    if not ligne_correcte(ligne):
        count+=1
        print(f"Ligne {numero}")


print(f"{count} lignes mauvaises")