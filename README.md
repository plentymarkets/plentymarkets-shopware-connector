# plentymarkets Shopware Connector

## Einleitung

Dieses shopware-Plugin ermöglicht die optimale Verbindung zwischen shopware und plentymarkets. Plentymarkets übernimmt dabei die führende Rolle in der Haltung ihrer Daten.

Sofern sie aktuell bereits shopware einsetzen ist die Verlagerung  der Daten auf plentymarkets recht einfach möglich, da hierzu ein eigener Prozess in diesem Plugin integriert wurde. Damit ist eine vollständige Datenübernahme u.a. der Artikelstammdaten, Aufträge oder Kundendaten von shopware zu plentymarkets möglich. Nach der Übertragung erfolgt die weitere Bearbeitung oder Pflege dieser Daten in plentymarkets.

Damit diese Daten in beiden System synchron gehalten werden können, nimmt das Plugin in bestimmten zeitlichen Intervallen ein Datenaustausch zwischen beiden Systemen vor. Hierbei werden beispielsweise neue Aufträge von shopware an plentymarkets übertragen oder Warenbestandsänderungen von plentymarkets zu shopware sofern es bei einem der angeschlossenen Marktplätze zu einem Verkauf und somit einer Warenbestandsänderung kam.

Dieses Plugin wird innerhalb von shopware installiert. Der Datenaustausch zwischen dem Plugin und plentymarkets erfolgt über die plentymarkets SOAP API. Ein Wechsel der plentymarkets Version ist somit unabhängig von einem Plugin-Update möglich, sofern die neue plentymarkets Version die SOAP API Version des installierten Plugins noch unterstützt. Im Regelfall werden plentymarkets SOAP Versionen für 12 Monate gewartet. Nach Ablauf dieser Zeit muss somit ein Wechsel auf die aktuelle Plugin-Version erfolgen. Nach aktueller Planung wird dieses Plugin dauerhaft kostenlos angeboten werden.

## Weiterentwicklung & Pflege

Sofern sie Funktionen vermissen: kein Problem! Der vollständige Programmcode ist offen über github verfügbar. Wenn sie eigene Erweiterungen vornehmen möchten, erstellen sie ein Fork unseres Repositories.

Wenn sie allgemeingültige Erweiterungen oder Korrekturen vornehmen möchten, senden sie uns einfach einen commit mit ihren Änderungen. Wir prüfen diese und übernehmen ihre Änderungen nach einer erfolgreichen Prüfung.

Eine dauerhafte Wartung dieses Plugins auf zukünftige plentymarkets SOAP API Versionen wird nach deren Freigabe zeitnah durch unser Entwicklerteam vorgenommen. Individuelle Erweiterung dieses Plugins können wir im Rahmen von Enterprise-Projekten vornehmen. In allen Anderen Fällen sind individuelle Erweiterungen auch über die von uns empfohlenen shopware-Agenturen möglich.

## Installation
### Systemvoraussetzungen
Für das Plugin sind min. **plentymarkets 5.0** und **Shopware 4.1** nötig. Innerhalb von Shopware muss das **Cron** Plugin installiert und aktiviert sein.

### Installation via github
#### Herunterladen als Archiv
Sie können den Quellcode als Archiv direkt und ohne Anmeldung von github herunterladen. Hierfür ist auf Ihrem Server keine Installtion von git erforderlich. Das Archiv muss anschließend in das Verzeichnis
	
	/Pfad/zu/shopware/engine/Shopware/Plugins/Local/Backend/Plentymarkets
	
extrahiert werden. Folgende Verzeichnisstruktur sollte danach vorhanden sein:

	+-shopware/
  		+-engine/
    		+-Shopware/
      			+-Plugins/
        			+-Local/
          				+-Backend/
            				+-Plentymarkets/
              					+-Components/
              					+-Controllers/
              					+-Views/

Anschließend kann das Plugin über den Plugin Manager installiert werden. Der Nachteil an dieser Variante ist, dass sie den Prozess immer wiederholen müssen, sobald es eine Änderung am Plugin gegeben hat.

#### Klonen des Repositories
Eine komfortablere Variante ist das klonen des Repositories mittels git. Hierfür muss git auf Ihrem Server installiert sein. Weitere Informationen dazu finden sie auf [der Hompage](http://git-scm.com/) von git.

Das Repository muss im Verzeichnis

	/Pfad/zu/shopware/engine/Shopware/Plugins/Local/Backend
	
geklont werden. Gehen Sie dazu wie folgt vor:

	cd /Pfad/zu/shopware/engine/Shopware/Plugins/Local/Backend
	git clone https://github.com/plentymarkets/plentymarkets-shopware-connector.git Plentymarkets
	
Das klonen beginnt:

	Cloning into 'Plentymarkets'...
	remote: Counting objects: 853, done.
	remote: Compressing objects: 100% (413/413), done.
	remote: Total 853 (delta 430), reused 853 (delta 430)
	Receiving objects: 100% (853/853), 200.64 KiB | 302 KiB/s, done.
	Resolving deltas: 100% (430/430), done.
	
Anschließend kann das Plugin über den Plugin Manager installiert werden. Sie haben nun den aktuellen Stand des Plugins installiert.

### Installation aus dem Community Store
Nach der Pilotphase kann das Plugin auch aus dem Shopware Community Store installiert werden.

## Einrichtung
### plentymarkets
Folgende Schritt müssen *vor* der Einrichtung des Plugins innerhalb Ihres plentymarkets System vorgenommen werden.
#### Mandant anlegen
#### Benutzer anlegen
Legen Sie unter **Einstellungen » Grundeinstellungen » Benutzer » Konten** einen neuen Benutzer an. Dieser Benutzer wird für die Kommunikation zwischen plentymarkets und Shopware über die SOAP API verwendet. Definieren Sie den Benutzer deshlab am besten vom Typ **API**.

#### Auftragsherkunft (optional)
#### Freitextfelder (optional)

### Shopware
Nach der Installation und Aktivierung des Plugins über den Plugin Manager müssen sie den Shop-Cache leeren und das Shopware-Fenster neu laden, damit der Menüpunkt **Einstellungen » plentymarkets** erscheint. **Achtung:** Bitte unbedingt an folgende Schritte halten!

#### Verbindung zu plentymarkets herstellen
Geben Sie unter dem Menüpunkt **API** die URL Ihres System sowie die Zugangsdaten des Benutzers ein, mit dem die Kommunikation stattfinden soll. Sie können die Verbindung prüfen, im dem Sie auf den entsprechenden Button *Zugangsdaten testen* klicken.

Nach dem Speichern von korekten Daten, werden die weiteren Reiter **Einstellungen**, **Mapping** und **Datenaustausch** aktiviert. Sobald keine Verbindung zu plentymarkets hergestellt werden kann, werden diese Reiter und jegliche Kommunikation automatisch deaktiviert!

#### Einstellungen
Pflicht (ohne diese kein Datenaustausch)

 * Mandant
 * Lager
 * Hersteller
 * Herkunft
 * Status: bezhalt
 * Status: versendet
 * Warenausgang (Art)
 * Warenausgang (Intervall)


#### Mapping
Für alle Daten, die nicht automatisch gemappt werden können, müssen sie die Verknüpfung manuel herstellen. **Wichtig:** Es müssen alle Datensätze gemappt werden! Außerdem kann jedem Datensatz von shopware genau ein Datensatz von plentymarktes zugeordner werden und andersrum. D.h. eine Mehrfachverwendung ist nicht möglich.

Wenn das Mapping nicht vollständig abgeschlossen ist oder im laufenden Betrieb weitere Datensätze innerhalb von Shopware erstellt werden, die manuell gemappt werden müssen, wird der gesamte Datenaustausch deaktiviert. Auf der Startseite des Plugins werden Sie auf diesen Umstand hingewiesen.

##### Währungen
Die Währungen werden Aufträgen zugeordnet, die von shopware zu plentymarkets exportiert werden. Da Aufträge nicht von plentymarkets zu shopware exportiert werden, ist es nicht erforderlich, alle Währungen von plentymarkets auch in Ihrem Shopware System anzulegen.

##### Einheiten
Die Einheiten sind den Artikeln zugeordnet. Da der Abgleich in beide Richtungen stattfindet, macht es Sinn, allen Einheiten, die sie bei plentymarkets verwenden auch bei Shopware anzulegen. Wenn eine Einheit innerhalb von shopware nicht zugeordnet werden kann, wird dem Artikel keine Einheit definiert.

##### Zahlungsarten
Die Zahlungsarten werden den Aufträgen zugeordnet.

**Wichtig:** Alle in plentymarkets mit einer Zahlungsart verbundenen Aktionen werden ausgeführt! D.h. wenn Sie als Zahlungsart Rechnung auswählen, wird der Auftrag nach dem Import in plentymarkets automatisch in den Status 4 bzw 5 gesetzt. Ebenso werden alle Events die ggf. konfiguriert sind ausgeführt.

**Achtung:** Die Zahlungsart *Vorkasse* kann derzeit nicht als eigenständige Zahlung in plentymarkets verwendet werden. Dieser werden über *Überweisung*, *HBCI* und *EBICS* abgedeckt.

##### Steuersätze
… sind zu hoch.

##### Versandarten

##### Länder
Die Länder werden den Anschriften (Rechnung- / Liefer-) zugeordnet. Kundendaten werden nur zu plentymarkets exportiert. Von daher ist es nicht erforderlich, alle plentymarkets-Länder zu mappen.

## Datenaustausch
Das Mapping für die Daten aus der Synchronisierung wird automatisch vorgenommen. 

**Achtung:** Der Datenaustauch darf nur gestartet weden, wenn alle unter Einrichtung genannten Punkte abgeschlossen sein!

### Initialer Export zu plentymarkets
Alle Artikeldaten aus Ihrem Shopware System müssen zu plentymarkets exportiert werden.

### Synchronisation
#### Artikelstammdaten
Die Artikelstammdaten werden stündlich abgeglichen. Der Abgleich beinhaltet folgende Bereiche:




#### Preise
 * erstes PriceSet
 * max 6 Staffelungen
 * nur für Shopkunden mit INdex EK

#### Warenbestände
Die Warenbestände werden alle 15 Minuten inkrementell abgeglichen. Alle Warenbestände von Stammartikeln und Varianten, die sich seit dem letzten Abgleich geändert haben, werden innerhalb von shopware angeglichen.

Sie können entweder ein bestimmes Lager als Quelle für die Warenbestände festlegen, oder das *virtuelle Gesamtlager* verwenden. Die Artikel in shopware erhalten dann den Warenbestand kumuliert aus allen vorhandenen Lagern.

#### Aufträge
Aufträge, die in shopware erstellt werden, werden alle 15 Minuten inkl. der Kundendaten nach plentymarkets exportiert. Die Versandkosten werden übernommen.

Wenn der Warenausgang in plentymarkets bebucht wird, wird der Auftag in shopware in den entsprechenden Status gesetzt. Sie können entweder einstellen, dass der Auftrag in plentymarkets einen bestimmen Status erreichen muss, um als Abgeschlossen zu gelten, oder ob es reicht, wenn der Warenausgang gebucht worden ist. Dieser Vorhang findet jede halbe Stunde statt.

Wenn ein Auftrag in shopware einen bestimmten Zahlungsstatus erreicht, wird in plentymarkets ein Zahlungseingang über den gesamten Rechnungsbetrag gebucht. Die Zahlungsart wird entsprechend der Einstellunge gesetzt. Dieser Vorhang findet stündlich statt.

**Achtung:** In plentymarkets kann ein Kunde nur eine Rechnungsadresse hinterlegen. Wenn ein und der selbe Kunde in shopware einen Auftrag mit unterschiedlichen Rechnungsadressen anlegt, wird er bei plentymarkets als neuer Kunde geführt.

## Log

Im Log erhalten Sie Auskunft über den Datentransfer. Jeder getätigte SOAP Call wird hier inkl. dessen Status (erfolgreich oder nicht) aufgeführt. Darüber hinaus erhalten Sie informationen, wenn bestimmte Datensätze angelegt worden sind oder bei der Verarbeitung Fehler aufgetreten sind. Es Wird generell zwischen Meldung und Fehler entschieden.

Jede Meldung (und jeder Fehler) hat ein bestimmtes Präfix. Dieses kennzeichnet grob, in welcher Prozess verantworlich ist. ES gibt die folgenden Präfixe:

* Export:Initial:<Entität>
* Export:Item
* Export:Item:Image
* Export:Item:Variant
* Export:Order
* Soap:Auth
* Soap:Call
* Sync:Item
* Sync:Item:Image
* Sync:Item:Linked
* Sync:Item:Price
* Sync:Item:Stock
* Sync:Item:Variant
* Sync:Order:OutgoingItems
* Sync:Order:Payment