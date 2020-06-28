# Poszeidon és Neptun API

## Update
Elindult az [Atlantisz](https://github.com/RuzsaGergely/Atlantisz) projekt, amelynek keretében egy proxy alá veszem a Krétát és a Neptunt. Természetesen rendszer és proxy dokumentációval ellátva. (Az átfogó Kréta dokumentáció már fent van)

## Bevezető

A lentebb olvasható információkat a hivatalos Neptun appból szedtem ki. A teszteléseket manuálisan végeztem Postman segítségével. A proxy-t PHP-ban írtam/írom, mert... mazoista(?) vagyok. 

Ha a Poszeidon proxyt szeretnéd használni, akkor a Releasekben találsz egy előre elkészített Docker konténert.

## Neptun API

### Intézmények lekérése

Az intézmények listáját erről az URL-ről lehet lekapni POST metódussal:

```
https://mobilecloudservice.cloudapp.net/MobileServiceLib/MobileCloudService.svc/GetAllNeptunMobileUrls
```

Ennek van annyi szépséghibája hogy, a szerver amiről a Neptun app lekéri az iskolák listáját annyira el van hanyagolva hogy <u>most (2019-ben) 3 éve lejárt SSL tanúsítványt használnak, de mindeközben követeli a HTTPS használatát</u>. Ki is halt tőle a Postman...

Ha minden jól ment (és sikerült áthidalni az SSL-es problémát) akkor egy, az E-krétához hasonló listát kell hogy kapjunk. Egy kis részlet belőle:

```json
...
	{
        "Languages": "HU,EN,DE,FR",
        "Name": "Budapesti Gazdasági Egyetem",
        "NeptunMobileServiceVersion": 0,
        "OMCode": "FI82314",
        "Url": "https://neptun3.uni-bge.hu/hallgato/MobileService.svc"
    },
    {
        "Languages": "HU,EN,DE",
        "Name": "Budapesti Metropolitan Egyetem",
        "NeptunMobileServiceVersion": 0,
        "OMCode": "FI33842",
        "Url": "https://neptunweb1.metropolitan.hu/hallgato/MobileService.svc"
    },
...
```

### Adatvédelmi nyilatkozat lekérése

Az adatvédelmi tájékoztatót így lehet lekérni (újfent POST methoddal).

```
https://<iskola-neptun-linkje>/<hallgatoi-api>/MobileService.svc/GetPrivacyStatement
```

Érdekes módon nem minden egyetemnek/főiskolának van adatvédelmi nyilatkozata rögzítve a Neptun rendszerben így például a BME egy `{"URL": null}`-t fog adni míg mondjuk egy Tan kapuja `{"URL": "https://www.tkbf.hu/foiskola/adatvedelmi-tajekoztato"}`-t.

### Egy fiókhoz tartozó képzések lekérése

A képzéseket így lehet lekérni, természetesen POST methoddal.

```
https://<iskola-neptun-linkje>/<hallgatoi-api>/MobileService.svc/GetTrainings
```

Az elküldött lekérésben ezek szerepelnek:

```json
{
	"OnlyLogin":false,
	"TotalRowCount":-1,
	"ExceptionsEnum":0,
	"UserLogin":"dummy",
	"Password":"dummypass",
	"NeptunCode":null,
	"CurrentPage":0,
	"StudentTrainingID":null,
	"LCID":1038,
	"ErrorMessage":null,
	"MobileVersion":"1.5",
	"MobileServiceVersion":0
}
```

- OnlyLogin => Nem teljesen értem a koncepciót mögötte, de ha "true"-ra állítom akkor nem kapom vissza a képzéseket.
- TotalRowCount => A -1 itt végtelent jelent szerintem, tehát bármennyi képzés van, ki kell hogy tudja adni.
- ExceptionsEnum => ha rájövök mi az, leírom.
- UserLogin => Neptunos felhasználónév.
- Password => Neptunos jelszó.
- NeptunCode => Ezt a bejelentkezés alapján kapjuk vissza, bár nem értem miért lehet elküldeni.
- CurrentPage => A lekérés eredményének aktuális oldala (pl. ha sok eredmény van, nem fér ki egy oldalra mert... csak...).
- StudentTrainingID => Ezt a bejelentkezés alapján kapjuk vissza, bár nem értem miért lehet elküldeni.
- LCID => Windows Language Code Identifier. 1038 decimálisban ami hexadecimálisban 0x040E. Ez a [Windows LCID dokumentáció](https://docs.microsoft.com/en-us/openspecs/windows_protocols/ms-lcid/70feba9f-294e-491e-b6eb-56532684c37f) szerint a 'hu-HU' tehát magyar kódolás.
- ErrorMessage => Nem értem a koncepciót mögötte (lásd később a verdiktnél).
- MobileVersion => Mivel az appból szedtem ki, így szinte biztos vagyok benne hogy, az a app verziójára utal
- MobileServiceVersion => Nem értem a koncepciót mögötte (lásd később a verdiktnél).

**<u>Verdikt:</u>** Megfigyeltem hogy, ha csak a `UserLogin` és `Password` mezőket hagyom bent a lekérésben akkor is  hibátlanul visszakapom a képzéseket. *Ipso facto, a program által használt lekérésnek a 3/4-de szemét.*

### Üzenetek lekérése

Az üzeneteket így lehet lekérni, természetesen POST methoddal.

```
https://<iskola-neptun-linkje>/<hallgatoi-api>/MobileService.svc/GetMessages
```

Az elküldött lekérésben ezek szerepelnek:

```json
{
	"TotalRowCount":-1,
	"ExceptionsEnum":0,
	"MessageID":0,
	"MessageSortEnum":0,
	"UserLogin":"dummy",
	"Password":"dummypass",
	"NeptunCode":"DUMMY",
	"CurrentPage":0,
	"StudentTrainingID":123456789,
	"LCID":1038,
	"ErrorMessage":null,
	"MobileVersion":"1.5",
	"MobileServiceVersion":0
}
```

A fentebb írtak itt is relevánsak. A `UserLogin` és a `Password` szükséges csak. 

A `MessageID`-val tudunk egyetlen üzenetre rámutatni. Ha 0-ás akkor minden üzenetet kilistáz az adott oldalon (lásd `CurrentPage` ), de ha megadunk egy ID-t neki (pl.: 866172401) akkor azt az egy üzenetet fogja kiírni.

Emellett fontos a `CurrentPage` is mert ha több oldalon keresztül van üzenet akkor ezzel tudjuk módosítani hogy, melyik oldalról szeretnénk lekérni a leveleket. 

A `MessageSortEnum` paraméterrel tudjuk állítani azt hogy a leveleket milyen módon rendezze.

- 0 => Időrendben: Legújabbtól a legrégebbi üzenetig.
- 1 => Időrendben: Legrégebbitől a legújabb üzenetig.
- 2 => Küldő neve szerint: Z-A
- 3 => Küldő neve szerint: A-Z 
- 4 és afelett => Nem tudtam egyértelműen megállapítani. (befejezetlen funkció?)

Rövidítve valahogy így nézhet ki a lekérés:

```json
{
	"MessageID":0,
	"MessageSortEnum": 0,
	"UserLogin":"dummy",
	"Password":"dummy",
	"CurrentPage":0
}
```

### Naptár lekérése

A naptárt így lehet lekérni, természetesen POST methoddal.

```
https://<iskola-neptun-linkje>/<hallgatoi-api>/MobileService.svc/GetCalendarData
```

Az elküldött lekérésben ezek szerepelnek:

```json
{
	"needAllDaylong":false,
	"TotalRowCount":-1,
	"ExceptionsEnum":0,
	"Time":true,
	"Exam":true,
	"Task":true,
	"Apointment":true,
	"RegisterList":true,
	"Consultation":true,
	"startDate":"\/Date(1561922719265)\/",
	"endDate":"\/Date(1588274719265)\/",
	"entityLimit":0,
	"UserLogin":"dummy",
	"Password":"dummypass",
	"NeptunCode":"DUMMY",
	"CurrentPage":0,
	"StudentTrainingID":123456789,
	"LCID":1038,
	"ErrorMessage":null,
	"MobileVersion":"1.5",
	"MobileServiceVersion":0
}
```

A fentiek itt is relevánsak, habár nem csak a `UserLogin` és `Password` fog itt kelleni. A `Time`, `Exam`, `Task`, `Appointment`, `RegisterList`, `Consultation` kulcsok true/false értékek. Ezekkel lehet szűrni a naptár tartalmát. A `startDate` és `endDate` határozza meg a naptár időintervallumját. Fontos kihangsúlyozni hogy, a dátumok Epoch időben van megadva. ([Itt egy konverter hozzá](https://www.online-toolz.com/tools/date-functions.php)). Az `entityLimit` nem tudom hogy, mit csinál. Ha 0 akkor visszaad értékeket, más egyéb értékre semmit se ad vissza. (rossz implementáció?)

Rövidítve valahogy így nézhet ki a lekérés:

```json
{
	"needAllDaylong":false,
	"Time":true,
	"Exam":true,
	"Task":true,
	"Apointment":true,
	"RegisterList":true,
	"Consultation":true,
	"startDate":"\/Date(1561922719265)\/",
	"endDate":"\/Date(1588274719265)\/",
	"UserLogin":"dummy",
	"Password":"dummypass"
}
```

### Időszakok lekérése

Az időszakokat így lehet lekérni, természetesen POST methoddal.

```
https://<iskola-neptun-linkje>/<hallgatoi-api>/MobileService.svc/GetPeriodTerms
```

Az elküldött lekérésben ezek szerepelnek:

```json
{
	"TotalRowCount":-1,
	"ExceptionsEnum":0,
	"UserLogin":"dummy",
	"Password":"dummypass",
	"NeptunCode":null,
	"CurrentPage":0,
	"StudentTrainingID":null,
	"LCID":1038,
	"ErrorMessage":null,
	"MobileVersion":"1.5",
	"MobileServiceVersion":0
}
```

A fentebb írtak itt is relevánsak. A `UserLogin` és a `Password` szükséges csak, de a `CurrentPage` hasznos lehet felhasználáskor.

Rövidítve valahogy így nézhet ki:

```json
{
	"UserLogin":"dummy",
	"Password":"dummypass",
	"CurrentPage":0
}
```

### Időszak részleteinek lekérése

Az időszakok részeit így lehet lekérni, természetesen POST methoddal.

```
https://<iskola-neptun-linkje>/<hallgatoi-api>/MobileService.svc/GetPeriods
```

Az elküldött lekérésben ezek szerepelnek:

```json
{
	"PeriodTermID":70619,
	"TotalRowCount":-1,
	"ExceptionsEnum":0,
	"UserLogin":"dummy",
	"Password":"dummypass",
	"NeptunCode":null,
	"CurrentPage":0,
	"StudentTrainingID":null,
	"LCID":1038,
	"ErrorMessage":null,
	"MobileVersion":"1.5",
	"MobileServiceVersion":0
}
```

A fentiek itt is relevánsak, annyi kiegészítéssel hogy, a `UserLogin` és a `Password` kulcs mellett szükséges egy `PeriodTermID` megadása is. Ezzel tudjuk meghatározni hogy, melyik időszak információira vagyunk kíváncsiak. 

Rövidítve valahogy így nézhet ki:

```json
{
	"PeriodTermID":70619,
	"ExceptionsEnum":0,
	"UserLogin":"dummy",
	"Password":"dummypass",
	"CurrentPage":0
}
```

## Poszeidon használata

### Bevezető

Ezt a kis proxyt azért írtam hogy, a jövőben egyszerűben lehessen a Neptun rendszerét használni, ezzel elősegítve a különféle appokat, webappokat és gadgetek fejlesztését. 

Az összes lekérés `POST` metódussal történik, valamint a Poszeidon által visszaadott respone megfelel a Neptun API responsenak, azt nem szűri.

### Intézmények lekérése

```
http://localhost/poszeidon/proxy/v1/GetInstitutes/
```

Ennek a lekéréséhez nem kell semmilyen adatot elküldeni (nem is fogad/dolgoz fel).

### Adatvédelmi nyilatkozat lekérése

```
http://localhost/poszeidon/proxy/v1/GetPrivacyStatement/
```

Ehhez a lekéréshez az alábbi adatokat kell megadni:

```json
{
	"url": "https://<iskola-neptun>/<hallgatoi-api>/MobileService.svc"
}
```

### Egy fiókhoz tartozó képzések lekérése

```
http://localhost/poszeidon/proxy/v1/GetTrainings/
```

Ehhez a lekéréshez az alábbi adatokat kell megadni:

```json
{
	"url": "https://<iskola-neptun>/<hallgatoi-api>/MobileService.svc",
	"userlogin": "dummy",
	"password": "dummypass"
}
```

### Üzenetek lekérése

```
http://localhost/poszeidon/proxy/v1/GetMessages/
```

Ehhez a lekéréshez az alábbi adatokat kell megadni:

```json
{
	"url": "https://<iskola-neptun>/<hallgatoi-api>/MobileService.svc",
	"userlogin": "dummy",
	"password": "dummypass"
}
```

Ez a lekérés az alapvető, viszont egy bővített változatban akár az egy levélre való szűrést, oldal változtatást és rendezést is be tudunk állítani. 

A bővített lekérés így néz ki:

```json
{
	"url": "https://<iskola-neptun>/<hallgatoi-api>/MobileService.svc",
	"userlogin": "dummy",
	"password": "dummypass",
	"messageID": 0,
	"currentPage": 0,
	"messageSortNum": 0
}
```

### Naptár lekérése

```
http://localhost/poszeidon/proxy/v1/GetCalendarData/
```

Ehhez a lekéréshez az alábbi adatokat kell megadni:

```json
{
	"url": "https://<iskola-neptun>/<hallgatoi-api>/MobileService.svc",
	"userlogin": "dummy",
	"password": "dummypass"
}
```

Ez a lekérés az alapvető, de sokkal több paramétert lehet módosítani. Egy bővített lekérés így néz ki:

```json
{
	"url": "https://<iskola-neptun>/<hallgatoi-api>/MobileService.svc",
	"userlogin": "dummy",
	"password": "dummypass",
	"currentPage": 1,
	"allDayLong": true,
	"Time": false,
	"Exam": false,
	"Task": false,
	"Appointment": false,
	"RegisterList": false,
	"Consultation": false
}
```

Az `AllDayLong` paramétert leszámítva az összesnek 'true' az alapvető értéke, valamint a `CurrentPage` az minden esetben 0.

### Időszakok lekérése

```
http://localhost/poszeidon/proxy/v1/GetPeriodTerms/
```

Ehhez a lekéréshez az alábbi adatokat kell megadni:

```json
{
	"url": "https://<iskola-neptun>/<hallgatoi-api>/MobileService.svc",
	"userlogin": "dummy",
	"password": "dummypass"
}
```

Ha esetleg nem férne ki egy lekérésbe, mert annyi időszak van valamiért nyilvántartva akkor a `currentPage` kulcs hozzáadásával lehet szabályozni.

### Időszak részleteinek lekérése

```
http://localhost/poszeidon/proxy/v1/GetPeriodData/
```

Ehhez a lekéréshez az alábbi adatokat kell megadni:

```json
{
	"url": "https://<iskola-neptun>/<hallgatoi-api>/MobileService.svc",
	"userlogin": "dummy",
	"password": "dummypass",
	"periodID": "00000"
}
```

Opcionálisan a `currentPage` kulcs itt is játszik, ha több oldalas lenne a lekérés eredménye.
