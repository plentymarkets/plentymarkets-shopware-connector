![plentymarkets Logo](http://www.plentymarkets.eu/layout/pm/images/logo/plentymarkets-logo.jpg)
# plentymarkets shopware connector

- **License**: AGPL v3
- **Github Repository**: <https://github.com/plentymarkets/plentymarkets-shopware-connector>

## Inhalt

- [Einleitung](#einleitung)
- [Weiterentwicklung & Pflege](#weiterentwicklung--pflege)
- [Installation](#installation)
	- [Systemvoraussetzungen](#systemvoraussetzungen)
		- [PHP](#php)
	- [Installation via github](#installation-via-github)
		- [Herunterladen als Archiv](#herunterladen-als-archiv)
		- [Klonen des Repositories](#klonen-des-repositories)
	- [Installation aus dem Community Store](#installation-aus-dem-community-store)
- [Einrichtung](#einrichtung)
	- [plentymarkets](#plentymarkets)
		- [Mandant (Shop) anlegen](#mandant-shop-anlegen)
		- [Benutzer anlegen](#benutzer-anlegen)
		- [Auftragsherkunft (optional)](#auftragsherkunft-optional)
		- [Freitextfelder (optional)](#freitextfelder-optional)
	- [shopware](#shopware)
		- [Verbindung zu plentymarkets herstellen](#verbindung-zu-plentymarkets-herstellen)
		- [Einstellungen](#einstellungen)
			- [Import Artikelstammdaten](#import-artikelstammdaten)
			- [Export Aufträge](#export-auftr%C3%A4ge)
			- [Warenausgang](#warenausgang)
			- [Zahlungseingang bei plentymarkets](#zahlungseingang-bei-plentymarkets)
		- [Mapping](#mapping)
			- [Währungen](#w%C3%A4hrungen)
			- [Einheiten](#einheiten)
			- [Zahlungsarten](#zahlungsarten)
			- [Steuersätze](#steuers%C3%A4tze)
			- [Kundengruppen](#kundengruppen)
			- [Versandarten](#versandarten)
			- [Länder](#l%C3%A4nder)
		- [Cron](#cron)
- [Datenaustausch](#datenaustausch)
	- [Initialer Export zu plentymarkets](#initialer-export-zu-plentymarkets)
		- [Kategorien](#kategorien)
		- [Konfigurator / Attribute](#konfigurator--attribute)
		- [Eigenschaften / Merkmale](#eigenschaften--merkmale)
		- [Hersteller](#hersteller)
		- [Artikel](#artikel)
		- [Kunden](#kunden)
	- [Synchronisation](#synchronisation)
		- [Artikel](#artikel-1)
			- [Stammdaten](#stammdaten)
			- [Varianten](#varianten)
			- [Preise](#preise)
			- [Bilder](#bilder)
			- [Ähnliche Artikel & Zubehör (Cross-Selling)](#%C3%84hnliche-artikel--zubeh%C3%B6r-cross-selling)
			- [Warenbestände](#warenbest%C3%A4nde)
		- [Aufträge](#auftr%C3%A4ge)
- [Log](#log)
	- [Meldungen](#meldungen)
	- [Fehler](#fehler)

## Einleitung

Dieses shopware-Plugin ermöglicht die optimale Verbindung zwischen shopware und plentymarkets. plentymarkets übernimmt dabei die führende Rolle in der Haltung der gesamten Daten des jeweiligen Nutzers.

Für Kunden, die aktuell bereits shopware einsetzen, ist die Überführung der Daten hin zu plentymarkets recht einfach möglich, da hierzu ein eigener Prozess in das Plugin integriert wurde. Damit ist eine vollständige Datenübernahme, u.a. der Artikelstammdaten, Aufträge oder Kundendaten, von shopware zu plentymarkets möglich. Nach der Übertragung erfolgt die weitere Bearbeitung und Pflege dieser Daten in plentymarkets.

Damit die Daten in beiden Systemen synchron gehalten werden, nimmt das Plugin in bestimmten zeitlichen Intervallen einen Datenaustausch zwischen den beiden Systemen vor. Hierbei werden beispielsweise neue Aufträge von shopware an plentymarkets übertragen oder Warenbestandsänderungen von plentymarkets an shopware gemeldet, sofern es bei einem der angeschlossenen Marktplätze zu einem Verkauf und somit zu einer Warenbestandsänderung kam.

Das Plugin wird innerhalb von shopware installiert. Der Datenaustausch zwischen dem Plugin und plentymarkets erfolgt über die plentymarkets SOAP API. Ein Wechsel der plentymarkets-Version ist somit unabhängig von einem Plugin-Update möglich, sofern die neue plentymarkets-Version die SOAP API-Version des installierten Plugins noch unterstützt. Im Regelfall werden SOAP-Versionen von plentymarkets für 12 Monate gewartet. Nach Ablauf dieser Zeit muss somit ein Wechsel auf die aktuelle Plugin-Version erfolgen. Nach aktueller Planung wird das Plugin dauerhaft kostenlos angeboten werden.

## Weiterentwicklung & Pflege

Für Anwender, die spezielle Funktionen vermissen, ist der vollständige Programmcode offen über github verfügbar. Um eigene Erweiterungen vorzunehmen, muss ein Fork unseres Repositories erstellt werden.

Des Weiteren besteht die Möglichkeit, Vorschläge für allgemeingültige Erweiterungen oder Korrekturen per commit an plentymarkets zu senden. Wir prüfen diese und übernehmen die Änderungsempfehlungen nach einer erfolgreichen Prüfung.

Eine dauerhafte Wartung des Plugins auf zukünftige plentymarkets SOAP API-Versionen wird nach deren Freigabe zeitnah durch unser Entwicklerteam vorgenommen. Individuelle Erweiterungen dieses Plugins können wir im Rahmen von Enterprise-Projekten vornehmen. In allen anderen Fällen sind individuelle Erweiterungen auch über die von uns empfohlenen shopware-Agenturen möglich.

## Installation
### Systemvoraussetzungen
Dieses Plugin benötigt **plentymarkets 5.0** und **shopware 4.1**. Innerhalb von shopware muss das Plugin **Cron** installiert und aktiviert sein. Die Einrichtung dieses Plugins ist [hier](#cron) beschrieben.
Weiterhin muss in shopware mindestens ein Hersteller vorhanden sein.

#### PHP

Besonders für den automatischen Datenabgleich via Cron werden ausreichende System-Ressourcen benötigt. Es muss daher sichergestellt sein, dass PHP-Prozesse ausreichend lang laufen und genügend Speicher verbrauchen dürfen. Im Testbetrieb haben wir für PHP-Prozesse, welche per Shell (CLI) ausgeführt werden, diese Eckdaten verwendet:

	max_execution_time = 3600
	max_input_time = 1080
	memory_limit = 2048M

Ändern Sie diese Angaben in der korrekten php.ini-Datei. Diese finden Sie in per Shell im Standardfall an der folgenden Position

	/etc/php5/cli/php.ini

Grundsätzlich sollten Änderungen an Konfigurationsdateien nur von Ihrem Systemadministrator vorgenommen werden.

#### Apache & mod_fcgid

Wenn Sie das Modul **mod_fcgid** des **Apache** Webservers nutzen (und die Cronjobs über den Browser starten), müssen Sie dort ebenfalls die Laufzeit wie folgt erhöhen:

	<IfModule mod_fcgid.c>
		FcgidIOTimeout 3600
	</IfModule>

Weitere Information dazu finden Sie in der Dokumentation des Modules unter [httpd.apache.org](http://httpd.apache.org/mod_fcgid/mod/mod_fcgid.html). Grundsätzlich sollten Änderungen an Konfigurationsdateien nur von Ihrem Systemadministrator vorgenommen werden.
	

### Installation via github
#### Herunterladen als Archiv
Der Quellcode kann direkt und ohne Anmeldung als zip-Archiv von github heruntergeladen werden. Hierfür ist auf Ihrem Server keine Installation von git erforderlich. Das Archiv muss anschließend in das Verzeichnis
	
	/Pfad/zu/shopware/engine/shopware/Plugins/Local/Backend/Plentymarkets
	
extrahiert werden. Folgende Verzeichnisstruktur sollte danach vorhanden sein:

	+-shopware/
  		+-engine/
    		+-shopware/
      			+-Plugins/
        			+-Local/
          				+-Backend/
            				+-Plentymarkets/
              					+-Components/
              					+-Controllers/
              					+-Views/

Anschließend kann das Plugin über den Plugin Manager installiert werden. Der Nachteil an dieser Variante ist, dass der Prozess immer wiederholt werden muss, sobald es eine Änderung am Plugin gegeben hat.

#### Klonen des Repositories
Eine komfortablere Variante ist das Klonen des Repositorys mittels git. Hierfür muss git auf dem entsprechenden Server installiert sein. Weitere Informationen dazu finden sie auf [der Hompage](http://git-scm.com/) von git.

Das Repository muss in folgendes Verzeichnis geklont werden:

	/Pfad/zu/shopware/engine/Shopware/Plugins/Local/Backend
	
Gehen Sie dazu wie folgt vor:

	cd /Pfad/zu/shopware/engine/Shopware/Plugins/Local/Backend
	git clone https://github.com/plentymarkets/plentymarkets-shopware-connector.git Plentymarkets
	
Das Klonen beginnt:

	Cloning into 'Plentymarkets'...
	remote: Counting objects: 853, done.
	remote: Compressing objects: 100% (413/413), done.
	remote: Total 853 (delta 430), reused 853 (delta 430)
	Receiving objects: 100% (853/853), 200.64 KiB | 302 KiB/s, done.
	Resolving deltas: 100% (430/430), done.
	
Anschließend kann das Plugin über den Plugin Manager installiert werden. Daraufhin ist der aktuelle Stand des Plugins installiert. Um später eine Aktualisierung durchzuführen, muss in dem entsprechenden Verzeichnis der folgende Befehl ausgeführt werden:

	cd /Pfad/zu/shopware/engine/Shopware/Plugins/Local/Backend/Plentymarkets
	git pull origin master
	
Das Plugin muss jetzt ggf. über den Plugin Manager aktualisiert werden.

### Installation aus dem Community Store
Nach der Pilotphase kann das Plugin auch aus dem shopware Community Store installiert werden.

## Einrichtung
### plentymarkets
Folgende Schritte müssen *vor* der Einrichtung des Plugins innerhalb des plentymarkets-Systems ausgeführt werden.

#### Mandant (Shop) anlegen
In plentymarkets muss ein besonderer **Mandant (Shop)** angelegt werden. Ein Mandant (Shop) dient in plentymarkets dazu, verschiedene Einstellungen gesondert pro Mandant definieren zu können. So müssen Artikelstammdaten im Shop für jeden Mandant (Shop) aktiviert werden (dies geht auch per Gruppenfunktion im Bereich der Artikelsuche). Weiterhin können pro Mandant (Shop) andere E-Mail-Vorlagen versendet werden oder auch das Layout für Rechnung und Lieferscheine getrennt abgelegt werden.

Einen neuen Mandant (Shop) für shopware legen Sie in Ihrem plentymarkets System in diesem Bereich an:
**Einstellungen » Mandant (Shop) » Neuer externer Shop**

Sie müssen hier lediglich einen Namen eingeben und bei Typ die Auswahl shopware vornehmen.
Nachdem Sie den Mandant (Shop) erfolgreich angelegt haben, können Sie diesen Mandant (Shop) direkt innerhalb des shopware Plugins im Bereich Einstellungen auswählen.

#### Benutzer anlegen
Legen Sie unter **Einstellungen » Grundeinstellungen » Benutzer » Konten** einen neuen Benutzer an. Dieser Benutzer wird für die Kommunikation zwischen plentymarkets und shopware über die SOAP API verwendet. Nutzen Sie für den Benutzer deshalb am besten die Typ-Bezeichnung **API**.

Die folgende SOAP-Calls werden vom Plugin genutzt und müssen für somit auch für den Benutzer aktiviert werden.
Fehlen einzelne Berechtigungen, kann der Datenabgleich zwischen plentymarkets und shopware nicht vollständig ablaufen und es kann zu einem Datenverlust kommen.

* AddCustomerDeliveryAddresses
* AddCustomers
* AddIncomingPayments
* AddItemAttribute
* AddItemAttributeValueSets
* AddItemCategory
* AddItemsBase
* AddItemsImage
* AddLinkedItems
* AddOrders
* AddProperty
* AddPropertyGroup
* AddPropertyToItem
* GetAttributeValueSets
* GetCurrentStocks
* GetItemAttributes
* GetItemCategoryCatalogBase
* GetItemsBase
* GetItemsByStoreID
* GetItemsImages
* GetItemsPriceUpdate
* GetLinkedItems
* GetMethodOfPayments
* GetMultiShops
* GetOrderStatusList
* GetPlentyMarketsVersion
* GetProducers
* GetPropertiesList
* GetSalesOrderReferrer
* GetShippingProfiles
* GetShippingServiceProvider
* GetVATConfig
* GetWarehouseList
* SearchOrders
* SetAttributeValueSetsDetails
* SetProducers

Auf der folgenden Seite erfahren Sie, wie Sie die Berechtigungen in plentymarkets einrichten können:
http://man.plentymarkets.eu/einstellungen/grundeinstellungen/benutzer/benutzer-bearbeiten/

#### Auftragsherkunft (optional)
Soll den von shopware zu plentymarkets exportierten Aufträgen eine individuelle Herkunft zugeordnet werden, muss diese zuvor in plentymarkets unter **Einstellungen » Aufträge » Auftragsherkunft** angelegt werden.

Weitere Informationen zur Auftragsherkunft finden Sie auf dieser Seite:
http://man.plentymarkets.eu/einstellungen/auftraege/auftragsherkunft/

#### Freitextfelder (optional)
Um die Freitextfelder/Attribute der Artikel aus shopware zu übernehmen, müssen diese in plentymarkets unter **Einstellungen » Artikel » Freitextfelder** definiert werden.

Weitere Informationen zu Freitextfeldern finden Sie auf dieser Seite:
http://man.plentymarkets.eu/einstellungen/artikel/freitextfelder/

### shopware
Nach der Installation und Aktivierung des Plugins über den Plugin Manager muss der Shop-Cache geleert und das shopware-Fenster neu geladen werden, damit der Menüpunkt **Einstellungen » plentymarkets** erscheint.

**Wichtig:** Damit das Plugin ordnungsgemäß arbeiten kann, müssen die folgenden Schritte genau eingehalten werden.

#### Verbindung zu plentymarkets herstellen
Geben Sie unter dem Menüpunkt **API** die URL Ihres System sowie die Zugangsdaten des Benutzers ein, mit dem die Kommunikation stattfinden soll. Sie können die Verbindung prüfen, im dem Sie auf den entsprechenden Button *Zugangsdaten testen* klicken.

**Achtung:** Das Speichern impliziert ein Testen der Zugangsdaten. Sowohl die Funktion *Zugangsdaten testen* als auch das Speichern führen dazu, dass der SOAP Call GetAuthentificationToken aufgerufen wird. Dieser Call ist auf 30 Anfragen pro Tag und Benutzer limitiert! Werden diese Funktionen wiederholt genutzt, kann es sein, dass das Abrufen des Tokens bis zum Ende des Tages gesperrt wird.

Nach dem Speichern von korrekten Daten, werden die weiteren Reiter **Einstellungen**, **Mapping** und **Datenaustausch** aktiviert. Sobald keine Verbindung zu plentymarkets hergestellt werden kann, werden diese Reiter und jegliche Kommunikation automatisch deaktiviert!

#### Einstellungen
Die Einstellungen in den im Folgenden genannten Bereichen *müssen* vorgenommen werden, da ohne sie der Datenaustausch nicht stattfinden kann. Es wird eine Fehlermeldung erzeugt, wenn ein Datenaustausch gestartet werden soll, und die folgenden Einstellungen nicht gesetzt sind.

Die Daten, die von plentymarkets geladen werden, werden für einen Tag gespeichert. Sie können jedoch über den entsprechenden Button erneut abgefragt werden. **Achtung:** Manche der zu Grunde liegenden Calls sind auf eine bestimmte Anzahl (i.d.R. 30/Tag/User) begrenzt!

##### Import Artikelstammdaten

Einstellung | Erklärung
----|------
plentymarkets Lager | Datenquelle für den Warenbestandsabgleich  
Warenbestandspuffer | Prozentualer Anteil des netto-Warenbestandes des gewählten Lagers, welcher an shopware übertragen wird.
Mandant (Shop) | Das aktuelle shopware-System muss mit einem plentymarkets Mandant (Shop) verknüpft werden. Ein solcher *Mandant (Shop)* kann in plentymarkets über **Einstellungen » Mandant (Shop) » Neuer externer Shop** angelegt werden. 
Hersteller | Sofern bei Artikeln in plentymarkets kein Hersteller zugeordnet wurde, wird dieser Hersteller in shopware mit den betreffenden Artikeln verknüpft.
Kategorie Startknoten | Ausgangspunkt für den Export und den Abgleich der Kategorien. Diese Kategorie selbst wird *nicht* bei plentymarkets angelegt. Neue Kategorien in plentymarkets werden an diese Kategorie angehangen.
Kategorien synchronisieren | Aktivieren, wenn die Kategorien von bestehenden Artikel synchronisiert werden sollen. Anderfalls werden Kategorien nicht bei der Synchronisation berücksichtigt. Bei neuen Artikeln werden die Kategorien immer synchronisiert.
Standard-Kundenklasse | Kundenklasse deren Preise von plentymarkerts zu shopware übertragen werden.
Bereinigen | Aktion die ausgeführt wird, wenn die Mandantenzuordnung bei plentymarkets gelöst wird oder kein Mapping für den Artikel vorhanden ist.

##### Export Aufträge

Einstellung | Erklärung
----|------
Markierung | Sofern hier eine Auswahl getroffen wird, werden neue Aufträge von shopware an plentymarkets exportiert und dabei mit dieser Markierung versehen. 
Auftragsherkunft | Die hier ausgewählte Auftragsherkunft erhalten Aufträge von shopware in plentymarkets. In plentymarkets kann dazu eine eigene Auftragsherkunft angelegt werden.
Status bezahlt | shopware Status, der signalisiert, dass der Auftrag komplett bezahlt ist. Löst das Buchen des Zahlungseinganges bei plentymarkets aus.

##### Warenausgang

Einstellung | Erklärung
----|------
Warenausgang | Aufträge welche diese Regel erfüllen, werden von plentymarkets abgerufen, um die folgenden Statusänderungen in shopware zu bewirken.
Auftragsstatus | Erreicht ein Auftrag in plentymarkets diesen Auftragsstatus, gilt dieser als versendet.
Abfrageintervall | Zeitintervall für den Datenabgleich der Auftragsdaten 
shopware Auftragsstatus | Dieser Auftragsstatus wird gesetzt, wenn in plentymarkets der Warenausgang gebucht wurde.

##### Zahlungseingang bei plentymarkets

Einstellung | Erklärung
----|------
shopware Zahlungsstatus (komplett bezahlt) | Zahlungsstatus, welchen Aufträge erhalten, wenn diese innerhalb von plentymarkets als *komplett bezahlt* markiert werden.
shopware Zahlungsstatus (teilweise bezahlt) | Zahlungsstatus, welchen Aufträge erhalten, wenn diese innerhalb von plentymarkets als *teilweise bezahlt* markiert werden.

#### Mapping
Für alle Daten, die nicht automatisch gemappt werden können, muss die Verknüpfung manuell hergestellt werden.

**Wichtig:** Es müssen alle Datensätze gemappt werden! Jedem Datensatz in shopware kann genau ein Datensatz in plentymarktes zugeordnet werden und andersrum (1:1 Verknüpfung). Dementsprechend ist eine Mehrfachverwendung nicht möglich.

Wenn das Mapping nicht vollständig abgeschlossen ist oder im laufenden Betrieb weitere Datensätze innerhalb von shopware erstellt werden, die manuell gemappt werden müssen, wird der gesamte Datenaustausch deaktiviert. Auf der Startseite des Plugins wird der Nutzer auf diesen Umstand hingewiesen.

##### Währungen
Die Währungen werden Aufträgen zugeordnet, die von shopware zu plentymarkets exportiert werden. 
Da Aufträge nicht von plentymarkets zu shopware exportiert werden, ist es nicht erforderlich, alle Währungen aus plentymarkets auch im shopware-System anzulegen.

Weitere Informationen zu Währungen in plentymarkets finden Sie hier: http://man.plentymarkets.eu/einstellungen/auftraege/zahlung/waehrungen/

##### Einheiten
Die Einheiten sind den Artikeln zugeordnet. Da der Abgleich in beide Richtungen stattfindet, macht es Sinn, alle Einheiten, die in plentymarkets verwendet werden, auch bei shopware anzulegen. Wenn eine Einheit innerhalb von shopware nicht zugeordnet werden kann, wird für den Artikel keine Einheit definiert.

##### Zahlungsarten
In diesem Bereich müssen alle aktivierten Zahlungsarten in shopware mit einer entsprechenden Zahlungsart in plentymarkets verknüpft werden.
Sie sollten vor dem Mapping die Zahlungsarten deaktivieren, welche Sie nicht verwenden möchten. Nur wenn alle Zahlungsarten zugeordnet wurden, wird der Datenabgleich funktionieren.

**Wichtig:** Alle in plentymarkets für eine Zahlungsart eingerichteten Aktionen werden auch bei Aufträgen von shopware ausgeführt. Ist beispielsweise als Zahlungsart Rechnung ausgewählt, wird der Auftrag nach dem Import in plentymarkets automatisch in den Status 4 bzw. 5 gesetzt. 

**Achtung:** Bei Aufträgen mit der Zahlungsart *Vorkasse* wird nach der Zahlungseingangsbuchung ein Wechsel der Zahlungsart auf den tatsächlichen Zahlungsvorgang durchgeführt. In plentymarkets ändert sich somit die Zahlungsart dieser Aufträge nach dem Zahlungseingang auf HBCI oder EBICS, wenn beispielsweise ein automatischer Bankkonto-Abgleich aktiviert wurde.

Wie Sie Zahlungsarten in plentymarekts einrichten, finden Sie auf dieser Seite:
http://man.plentymarkets.eu/einstellungen/auftraege/zahlung/zahlungsarten/

##### Steuersätze
In diesem Bereich müssen die Umsatzsteuersätze von shopware mit den Umsatzsteuersätze von plentymarkets verknüpft werden. Umsatzsteuersätze in plentymarkets werden in dem Bereich **Einstellungen » Aufträge » Buchhaltung** verwaltet. 
Weitere Hilfe dazu: http://man.plentymarkets.eu/einstellungen/auftraege/buchhaltung/

##### Kundengruppen
Sofern hier die plentymarkets Standard-Kundenklassen *Standard-Endkunden* und/oder *Standard-Firmenkunden* nicht auftauchen, müssen diese in plentymarktes unter **Einstellungen » Kunden » Kundenklassen** einmalig abgespeichert werden.

##### Versandarten
Das Mapping der Versandarten dient dazu, die Funktionalitäten, die plentymarkets im Bereich Fulfillment bietet, vollumfänglich nutzen zu können.

Auf der folgenden Handbuchseite erfahren Sie, wie Sie Versandoptionen in plentymarkets einrichten können:
http://man.plentymarkets.eu/einstellungen/auftraege/versand/versandoptionen/

Erst durch die korrekte Einrichtung der Versandoptionen und das entsprechende Mapping von Bestellungen, welche über shopware zu plentymarkets gelangen, können Sie das Versand-Center sinnvoll nutzen. Das Versand-Center ist für die Anmeldung von Sendungen und der Generierung von Versand-Etiketten nötig.

Informationen zum plentymarkets Versand-Center finden Sie hier:
http://man.plentymarkets.eu/auftraege/versand/versand-center/

##### Länder
Die Länder werden den Anschriften (Rechnung- / Liefer-) zugeordnet. Kundendaten werden nur zu plentymarkets exportiert. Aus diesem Grund ist es nicht erforderlich, alle plentymarkets-Länder im entsprechenden shopware-System anzulegen.

Wie Sie Lieferländer in plentymarkets aktivieren können finden Sie hier:
http://man.plentymarkets.eu/einstellungen/auftraege/versand/versandoptionen/lieferlaender/

##### Partner / Auftragsherkunft
Die in shopware eingetragenen Partner können den plentymarkets Auftragsherkünften zugeordnet werden. Dieses ist vor allem sinnvoll, wenn die Artikel über die shopware Exportfunktion zu Preisportalen exportiert werden.

**Wichtig:** Dieses Mapping muss nicht vollständig durchgeführt werden. D.h. es muss nicht jedem Partner eine Auftragsherkunft zugeordnet sein. Fehlt eine solche Zuordnung, wird die in den Einstellungen des Connectors definierte Auftragsherkunft genommen.

Wie Sie eigene Auftragsherkünfte in plentymarkets anlegen, erfahren Sie hier:
http://man.plentymarkets.eu/einstellungen/auftraege/auftragsherkunft/#4

#### Cron
Der permanente Datenabgleich zwischen plentymarkets und shopware wird per Cronjob gesteuert. Dafür ist ein shopware-Plugin mit dem Namen *Cron* nötig. Dieses muss vorher installiert und konfiguriert werden. Beachten Sie dazu die Anleitung für dieses Plugin.

Die Ausführung des shopware-Cron-Prozesses muss über die zentrale crontab erfolgen. Sie erreichen diese über die folgenden Shell-Befehle:

    sudo su
    crontab -e

Gemäß der Anleitung des shopware-Plugins *Cron* erfolgt die Ausführung über eine Befehlskette nach dem folgenden Schema:

     cd /pfad/zumShop && /usr/bin/php shopware.php /backend/cron

Testen Sie die Ausführung dieses Befehls per Shell und übertragen Sie diesen erst dann in die crontab, wenn die Ausführung fehlerfrei funktioniert.

Im Standardfall sollte der Pfad */usr/bin/php* korrekt sein und auf PHP in Version 5.x verweisen. Bei manchen Providern kann dieser Pfad abweichen (z.B. */usr/bin/php5* oder */usr/bin/php53*).

Der crontab-Eintrag mit einer Ausführung alle 15 Minuten würde demnach diesem Schema folgen:

     */15 * * * * cd /pfad/zumShop && /usr/bin/php shopware.php /backend/cron

## Datenaustausch
Das Mapping für die Daten aus der Synchronisierung wird automatisch vorgenommen. 

**Achtung:** Der Datenaustauch darf (bzw. kann) nur gestartet werden, wenn alle unter Einrichtung genannten Punkte abgeschlossen sind. 
Sobald einer der erforderlichen Schritte noch nicht oder nur teilweise abgeschlossen ist, wird der Datenaustausch automatisch deaktiviert. Sobald alle Schritte abgeschlossen sind, wird der Datenaustausch wieder aktiviert.

Sollten Sie den Datenaustausch manuell deaktiviert haben, wird dieser niemals automatisch reaktiviert.

Der Datenauschtausch von shopware zu plentymarkets muss manuell gestartet werden. Der Import der Daten aus dem plentymarkets System findet automatisch statt, sobald der Datenaustausch aktiviert worden ist.

**Wichtig:** Es werden alle Artikel mit shopware abgeglichen, die unter **Artikel bearbeiten » Verfügbar » Mandant (Shop)** dem entsprechenden Shop zugeordnet sind. 

### Initialer Export zu plentymarkets
Alle Artikeldaten aus Ihrem shopware-System müssen zu plentymarkets exportiert werden. Das Übernehmen der Kundendaten ist optional und kann auch nachträglich noch gestartet werden.

Bevor Daten zu plentymarkets exportiert werden, werden die entsprechenden Daten einmalig abgerufen. Alle Datensätze die nun automatisch gemappt werden können, werden natürlich nicht erneut in plentymarkets angelegt. Das automatische Mapping findet nur bei **identischer** Schreibweise statt. D.h., dass *rot* nicht *Rot* zugeordnet wird.

#### Kategorien
Alle Kategorien, die unterhalb der als Startknoten definierten Kategorie liegen, werden zu plentymarkets exportiert. Es ist eine max. Tiefe von 6 Ebenen (exkl. des Startknotens, da diese nicht exportiert wird) möglich. Alles darüber hinaus kann bei plentymarkets nicht abgebildet werden.

Eine Kategorie, die in shopware in der 3. Ebene ist, wird bei plentymarkets entsprechend als Kategorie der Ebene 3 angelegt. Und kann dann auch nur dort verwendet werden.

**Achtung:** Es ist nicht zulässig, einem Artikel eine Kategorie zuzuordnen, die keine End-Kategorie ist (d.h. die Kategorie darf keine weiteren Unterkategorien haben).

#### Konfigurator / Attribute
Attribute und deren Werte werden zu plentymarkets übernommen.

**Achtung:** Attributwerte dürfen keine Kommas enthalten. Diese werden durch einen Punkt ersetzt.

#### Eigenschaften / Merkmale
Die Funktion der plentymarkts' Bestellmerkmale wird nicht abgebildet, da sie in shopware nicht vorhanden ist. Die Eigenschaften bzw. Merkmale in shopware haben keinen *echten* Datentyp. Aus diesem Grund muss der Typ des Merkmales in plentymarkets **Text** sein. Anderfalls würde Werte wie z. B. *1,5 Liter* verloren gehen, wenn ein nummerischer Typ eingestellt wäre.

Beim Import erstellte Merkmale werden vom Type **Text** erstellt und sind **keine** Bestellmerkmale. Die Werte von shopware werden nicht übernommen, da sie bei plentymarkets bei der Zuordnung zum Artikel hinterlegt werden.

#### Hersteller
Alle Hersteller die in shopware hinterlegt sind, werden zu plentymarkets übernommen.

#### Artikel
Alle Artikel, die aus dem shopware System in das plentymarkets System exportiert werden, werden automatisch mit dem entsprechenden Mandandten verknüpft.

#### Kunden
Der Export des Kundestammes ist optional und kann auch auch nachträglich durchgeführt werden. Es wird sowohl die Rechnungsanschrift als auch die Lieferanschrift übernommen.

### Synchronisation
Datensätze, die innerhalb von plentymarkets hinzugekommen sind, werden automatisch im shopware-System angelegt und gemappt. Das betrifft die folgenden Daten:

* Hersteller
* Kategorien
* Attribute (und Werte)
* Eigenschaften / Merkmale
* Artikel
* Bilder

**Wichtig:** Gelöschte Daten bleiben im shopware System bestehen! Eine Aktualisierung dieser Daten (insbesondere der Bezeichnungen) folgt.

Andersrum werden in plentymarkets u. U. folgende Daten durch die Synchronisation angelegt:

* Kunden und Lieferanschriften
* Aufträge
* Zahlungseingänge

#### Artikel
Es werden alle Artikel mit shopware abgeglichen, die unter **Artikel bearbeiten » Verfügbar » Mandant (Shop)** dem entsprechenden Shop zugeordnet sind.
Alle Artikel, die von shopware importiert worden sind, werden beim ersten Abgleich ignortiert (eine entsprechende Meldung wird im Log vermerkt).

**Hintergrund:** Es werden alle Artikel abgeglichen, die sich seit dem letzten Abgleich geändert haben. Der erste Abgleich – nach der aktivierung des Datenabgleiches – fragt **alle** Datensätze ab (bzw. alle, die sich seit bestehen des System geändert haben). Da die von shopware importierten Artikel natürlich *geändert* worden sind, werden als solche von der SOAP Schnittstelle ausgeliefert. Der erste Abgleich ignoriert diese Datensätze dann.

Die Artikelstammdaten werden stündlich abgeglichen. Der Abgleich beinhaltet folgende Bereiche:

##### Stammdaten
Die Funktion der plentymarkts' Bestellmerkmale wird nicht abgebildet, da sie in shopware nicht vorhanden ist. Zusätzlich kann einem Artikel in shopware nur genau eine Merkmal/Eigenschaftgruppe zugeordnet werden.

Die Artikelnummer wird nach dem Erstellen nicht mehr Synchronisiert. Eine Artikelnummer ist kein Pflichtfeld eines plentymarkets Artikels und muss auch nicht eindeutig sein. Da einem shopware-Artikel jedoch zwingend eine eindeutige Nummer zugeordnet werden muss, wird im Falle einer nicht eindeutigen Nummer eine fortlaufende Nummer vergeben. Das würde bei der nächsten Aktualisierung eines solchen Artikels erneut zu einer neuen Nummer führen.

shopware | plentymarkets | Anmerkung
---------|---------------|----------
Aktiv | Verfügbar » Inaktiv/Sichtbarkeit Webshop | Der Artikel wird aktiv gesetzt, wenn er bei plentymarkets nicht inaktiv ist, und der Artikel im Webshob sichtbar ist
Artikelnummer | Nr. | Wird generiert, wenn bei plentymarkets nicht vorhanden, oder bereits vergeben
Artikel hervorheben | Shop-Aktion | Nur der plentymarkets Wert *Top-Artikel* bewirkt, dass der Artikel hervorgehoben wird
n/a | Externe ID | Zusammengesetzt aus *Swag/* und der Id des shopware Artikels
Hersteller | Hersteller | Ist der Hersteller nicht vorhanden, wird er angelegt
EAN | EAN 1 | –
Gewicht (in KG) | Gewicht (Brutto) | Das Gewicht wird von gramm (plentymarkets) in kg umgerechnet
Maßeinheit | Einheit | Wird ensprechend gemappt. Ist die Einheit in shopware nicht vorhanden, wird keine zugeprdnet.
Inhalt | Inhalt | Beide System runden auf die 4. Nachkommastelle
Grundeinheit | VPE | plentymarkets arbietet hier nur mit ganzen Zahlen – shopware mit 3 Nachkommastellen
Verpackungseinheit | Einheit 1 | –
Breite | Breite | –
Höhe | Höhe | –
Länge | Länge | –
Artikel-Bezeichnung | Texte » Name 1 | –
Beschreibung | Texte » Artikeltext | –
Kurzbeschreibung | Texte » Vorschautext | –
Keywords | Texte » Keywords | –
zuletzt bearbeitet | – | Wird mit dem Zeiutpunkt der letzten Änderung am plentymarkets Artikel abgeglichen
Mindestabnahme | Verfügbar » Mindest-Bestellmenge
Maximalabnahme| Verfügbar » Maximale Bestellmenge
Staffelung | Verfügbar » Intervall-Bestellmenge
Abverkauf | Bestand » Beschränkung | Wird gesetzt, wenn die Verkäufe auf den Netto-Warenbestand beschränkt sind
Erscheinungsdatum | Erscheinungsdatum
? | Verfügbar bis
Zusatzfelder 1-20 | Felder 1-20 | 

##### Varianten
Der Abgleich der Varianten entspricht der Funktionalität **Standarddaten übernehmen**. D.h. alle Varianten werden mit dem Hauptartikel abgeglichen. Lediglich die Preise, die Artikelnummer und die EAN werden pro Variante gespeichert.

**Hinweis:** Bei einer Änderung an einer Variante oder einem Stammartikel werden immer sowohl der Stammartikel als auch alle Varianten synchronisiert.

shopware | plentymarkets | Anmerkung
---------|---------------|----------
Artikelnummer | Variantennummer | Wird generiert, wenn bei plentymarkets nicht vorhanden, oder bereits vergeben
EAN | EAN 1 | –


##### Preise
plentymarkets erlaubt eine Preisstaffelung bis zu einer Maximalanzahl von 6 Staffelungen. Varianten innerhalb von plentymarkets haben keine eigenen Preise; ebenso ist die Grundpreisberechnung vom Hauptartikel abhängig. Die Preise der Varianten ergeben sich aus den Preisen des Hauptartikels und den Aufpreisen der Attributwerte. Der sich daraus errechnete Preis wird für die Varianten innerhalb von shopware übernommen (inkl. Staffelung).

Es wird lediglich das erste in plentymarkets angelegte Preisset mit den Preisen für die unter Einstellungen eingestellte Kundengruppe synchronisiert. Einkaufspreise werden nicht übertragen.

shopware | plentymarkets | Anmerkung
---------|---------------|----------
Preis | Preis | Sofern nur der Preis angegeben ist, entspricht das bei shopware 1 - beliebig.
Staffelung | Preise 6 - 11 / Preis X ab Menge | Wird entsprechend als Staffelung angelegt
MwSt | MwSt. | entsprechend des Mappings

##### Bilder
Die Bilder werden in der shopware Medienbibliothek angelegt und dann dem Artikel zugeordnet. Das Bild, welches bei plentymarkets die niedrigste Position hat, wird bei shopware als Vorschaubild definiert.

**Achtung:** Bei einer Änderung an einem Artikel werden alle Bilder gelöscht und neu hinzugefügt.

##### Ähnliche Artikel & Zubehör (Cross-Selling)
Die Verknüpfungen zwischen Artikeln der Typen *ähnlicher Artikel* und *Zubehör* werden zu shopware übernommen. Alle anderen Typen werden ignoriert, da diese nicht von shopware unterstützt werden.

**Achtung:** Bei einer Änderung an einem Artikel werden alle Verknüpfungen neu hergestellt.

##### Warenbestände
Die Warenbestände werden alle 15 Minuten inkrementell abgeglichen. Alle Warenbestände von Stammartikeln und Varianten, die sich seit dem letzten Abgleich geändert haben, werden abgerufen und im shopware-System entsprechend angepasst.

Sie können entweder ein bestimmtes Lager als Quelle für die Warenbestände festlegen, oder das *virtuelle Gesamtlager* verwenden. Die Artikel erhalten in shopware dann den kumulierten Warenbestand aus allen vorhandenen Lagern.

In shopware wird immer der Netto-Warenbestand von plentymarktes gebucht. Dieser wird jedoch reduziert, wenn Sie unter Eintellungen den Warenbestandspuffer eingestellt haben (bzw. wenn der Wert kleiner als 100% ist). Dies dient zum Vermeiden von Überverkäufen, da die Warenbestände nicht immer aktuell sein können. Sofern ein positiver Warenbestand verfügbar ist, wird jedoch immer min. 1 gebucht.

#### Aufträge
Aufträge, die in shopware erstellt werden, werden alle 15 Minuten inkl. der Kundendaten nach plentymarkets exportiert. Die Versandkosten werden dabei mit übernommen.

Wenn der Warenausgang in plentymarkets gebucht wird, wird der Auftrag in shopware in den von Ihnen definierten Status gesetzt. Es kann entweder festgelegt werden, dass der Auftrag in plentymarkets einen bestimmen Status erreichen muss, um als abgeschlossen zu gelten, oder dass es reicht, wenn der Warenausgang gebucht worden ist. Dieser Abgleich findet einmal pro halber Stunde statt. Zusätzlich werden Zahlungen, die innerhlab von plentymarkets gebucht werden, zu shopware synchronisiert. Der Status für bezahlte und nur teilweise bezahlte Aufträge wird entsprechend der Einstellungen gesetzt.

Wenn ein Auftrag in shopwareden eingestellten Zahlungsstatus erreicht, wird in plentymarkets ein Zahlungseingang über den gesamten Rechnungsbetrag gebucht. Die Zahlungsart wird entsprechend der Einstellungen gesetzt. Dieser Vorgang findet stündlich statt.

**Achtung:** In plentymarkets kann ein Kunde nur eine Rechnungsadresse hinterlegen. Wenn ein und der selbe Kunde in shopware einen Auftrag mit abweichender Rechnungsadresse anlegt, wird er bei plentymarkets als neuer Kunde mit eigener ID geführt.

Artikelpositionen 

## Log
Im Log erhalten Sie Auskunft über den Datentransfer. Jeder getätigte SOAP Call wird hier inkl. dessen Status (erfolgreich oder nicht) aufgeführt. Darüber hinaus erhalten Sie Informationen, wenn bestimmte Datensätze angelegt wurden oder bei der Verarbeitung Fehler aufgetreten sind. Es wird hier generell zwischen Meldung und Fehler unterschieden.

Jede Meldung (und jeder Fehler) hat ein bestimmtes Präfix. Dieses kennzeichnet grundsätzlich, welcher Prozess verantwortlich ist. Es gibt die folgenden Präfixe:

Präfix | Erklärung | Richtung 
-------|-----------|---------
Cleanup:Item | Bereinigung der Artikel | →  shopware
Export:Initial:<Entität> | Initialer Export | →  plentymarkets
Export:Item | Initialer Export der Artikel | →  plentymarkets
Export:Item:Image | Bilder während des Exports der Artikel | →  plentymarkets
Export:Item:Variant | Varianten  während des Exports der Artikel | →  plentymarkets
Export:Order | Export von neuen Aufträgen zu plentymarkets | →  plentymarkets
Import:MethodOfPayment | Abfrage der Zahlungsarten | →  shopware
Import:Order:Status | Abfrage der Auftragsstatus | →  shopware
Import:Order:Referrer | Abfrage der Auftragsherkünfte | →  shopware
Import:Customer:Class | Abfrage der Kundenklassen | →  shopware
Import:Item:Warehouse | Abfrage der Lager | →  shopware
Import:Store | Abfrage der Mandanten (Shops) | →  shopware
Import:VAT | Abfrage der Steuersätze | →  shopware
Soap:Auth | Autentifizierung mit der SOAP Schnittstelle | →  plentymarkets
Soap:Call | Information ob ein SOAP Call erfolgreich gewesen ist | →  plentymarkets
Sync:Item | Sychronisation der Artikel | →  shopware
Sync:Item:Image | Sychronisation der Artikelbilder | →  shopware
Sync:Item:Linked | Sychronisation der Artikelverknüpfungen | →  shopware
Sync:Item:Price | Sychronisation der Preise | →  shopware
Sync:Item:Stock | Sychronisation der Warenbestände | →  shopware
Sync:Item:Variant | Sychronisation der Varianten | →  shopware
Sync:Order:OutgoingItems | Sychronisation der Zahlungseingänge| plentymarkets ↔ shopware
Sync:Order:IncomingPayment | Sychronisation des Warenausganges |→  shopware

### Meldungen
Bei Meldungen handelt es sich um normale Informationen und positive Rückmeldungen.

### Fehler
Sofern keine expliziete Fehlermeldung erscheint, wird idR. im darauffolgenen Eintrag im Log die Fehlermeldung des darunterliegenden Systems weitergegeben.

Präfix | Meldung | Erklärung
-------|---------|----------
Cleanup:Item | Meldung | Ein Artikel kann nicht deaktiviert werden
Cron:Export | Meldung | Wärend der Durchführung eines Cron Prozesses ist es zu einem unerwarteten Fehler gekommen. Der Fehler führt idR. dazu, das der Cron-Job deaktiviert wird.
Export:Initial:<Entität> | Announcement failed: `<REASON>` | Das Vormerken eines initialen Exportes ist fehlgeschlagen. Dies kann dadurch kommen, dass bereits ein Export läuft, vorgemekrt ist oder die Reihenfolge nicht eingehalten wird
Export:Initial:<Entität> | Fehlermeldung
Export:Item | ItemId `<ID>`: Item could not be exported | Der Artikel konnte nicht zu plentymarkets exportiert werden
Export:Item | ItemId `<ID>`: Skipping category with id `<CATEGOTY ID>` | Die Kategorie wird nicht exportiert, da sie entweder keine End-Kategorie (sondern ein Order) ist oder nicht unterhalb des ausgewählten Startknotens liegt
Export:Item:Image | ItemId `<ID>`: Skipping image with id `<IMAGE ID>` | Das Bild wird nicht exportiert
Export:Item:Image | ItemId `<ID>`: Skipping image with id `<IMAGE ID>` because there is no media associated | Das Bild ist fehlerhaft und wird nicht exportiert
Export:Item:Variant | ItemId `<ID>`: Skipping corrut variant with id `<VARIANT ID>` | Die Variante bzw. das Konfigurator-Set ist fehlerhaft
Import:Customer:Class | Customer classes could not be retrieved | Vermutlich ist das Call-Limit erreicht
Import:Item:Warehouse | Warehouses could not be retrieved | Vermutlich ist das Call-Limit erreicht
Import:MethodOfPayment | Methods of payment could not be retrieved | Vermutlich ist das Call-Limit erreicht
Import:Order:Referrer | Sales order referrer could not be retrieved | Vermutlich ist das Call-Limit erreicht
Import:Order:Status | Sales order statuses could not be retrieved | Vermutlich ist das Call-Limit erreicht
Import:Store | Stores could not be retrieved | Vermutlich ist das Call-Limit erreicht
Import:VAT | VAT could not be retrieved | Vermutlich ist das Call-Limit erreicht
Sync:Item | Item could not be created: `<Name>` | Der Artikel konnte nicht in shopware erstellt werden
Sync:Item | Item with the plentymarkets item id `<ID>` could not be updated | Der Artikel konnte nicht in shopware aktualisiert werden
Sync:Item | The property `<PropertyName>` could not be created | Die Eigenschaft konnte nicht erstellt werden
Sync:Item:Image | Got negative success from GetItemsImages for plentymarktes itemId `<ID>` | Die Bilder für den Artikel konnen nicht abgerufen werden
Sync:Item:Linked | Got negative success from GetLinkedItems for plentymarkets itemId `<ID>` | Die verknüpften Artikel konnten nicht abgerufen werden
Sync:Item:Linked | Item linker for the item with the plentymarkets item id `<ID>` failed | Das Verknüpfen der Atikel ist fehlgeschlagen
Sync:Order:IncomingPayment | The incoming payment could not be booked in plentymarkets because the sales order (`<ID>`) was not yet exported to plentymarkets | Der Zahlungseingang konnt nicht exportiert werden, da der Auftrag noch nicht in plentymarkets existiert
Sync:Order:IncomingPayment | The incoming payment of the sales order `<ID>` has already been exported to plentymarkets | Der Auftrag wurde bei plentymarkets als bezahlt markiert
Sync:Order:IncomingPayment | The incoming payment of the sales order `<ID>` was not booked in plentymarkets | Der Zahlungseingang konnt nicht gebucht werden
