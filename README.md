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
