// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/Misc}

/**
 * The settings view builds the graphical elements and loads all saved settings
 * data. It shows for example the chosen warhouse, the manufacturer or the order
 * status. The settings are differentiated into four groups: "Import
 * Artikelstammdaten", "Export Aufträge", "Warenausgang", "Zahlungseingang bei
 * plentymarkets". It is extended by the Ext form panel "Ext.form.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.Misc', {

	extend: 'Ext.form.Panel',

	alias: 'widget.plentymarkets-view-misc',

	title: '{s name=plentymarkets/view/misc/title}Verschiedenes{/s}',

	autoScroll: true,

	cls: 'shopware-form',

	layout: 'anchor',

	border: false,

	isBuilt: false,

	defaults: {
		anchor: '100%',
		margin: 10,
		labelWidth: '33%'
	},

	initComponent: function()
	{
		var me = this;
		me.init();
		me.callParent(arguments);
	},

	init: function()
	{
		var me = this;
		if (!me.isBuilt)
		{
			me.build();
		}
	},

	build: function()
	{
		var me = this;
		me.items = [{
			xtype: 'fieldset',
			title: 'Datenabgleich',
			defaults: {
				anchor: '100%',
				labelWidth: '33%'
			},
			items: [
                {
                    xtype: 'fieldcontainer',
                    fieldLabel: 'Artikel abgleichen',
                    layout: 'hbox',
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: '',
                            emptyText: 'plentymarkets Artikel ID',
                            id: 'plenty-itemId',
                            width: '50%',
                            allowBlank: true
                        },
                        {
                            xtype: 'button',
                            text: 'Jetzt abgleichen',
                            cls: 'primary small',
                            handler: function () {
                                var message = 'Möchten Sie, dass der Artikel mit der von Ihnen eingegeben plentymarkets Artikel ID sofort abgeglichen wird?';

                                Ext.Msg.confirm('Bestätigung erforderlich!', message, function (button) {
                                    if (button === 'yes') {
                                        Ext.Ajax.request({
                                            url: '{url action=syncItem}',
                                            callback: function (options, success, xhr) {
                                                Shopware.Notification.createGrowlMessage('Aktion ausgeführt', 'Der Artikel wurde aktualisiert');
                                            },
                                            jsonData: Ext.encode({
                                                itemId: Ext.getCmp('plenty-itemId').value
                                            })
                                        });
                                    }
                                });
                            }
                        }
                    ]
                },
                {
                    xtype: 'fieldcontainer',
                    fieldLabel: 'Detail ohne Nummer korrigieren',
                    layout: 'hbox',
                    items: [
                        {
                            xtype: 'button',
                            text: 'ausführen',
                            cls: 'secondary small',
                            handler: function () {
                                Ext.Ajax.request({
                                    url: '{url action=fixEmptyItemDetailNumber}',
                                    callback: function (options, success, xhr) {
                                        Shopware.Notification.createGrowlMessage('Aktion ausgeführt', 'Die Aktion wurde ausgeführt');
                                    }
                                });
                            }
                        }
                    ]
                },
			{
				xtype: 'fieldcontainer',
				fieldLabel: 'Alle Artikel abgleichen',
				layout: 'hbox',
				items: [{
					xtype: 'button',
					text: 'Vormerken',
					cls: 'secondary small',
					handler: function()
					{
						var message = 'Möchten Sie, dass alle Artikel mit plentymarkets abgeglichen werden?<br><br>Bitte beachten Sie, dass der Abgleich erst mit der Ausführung des Cronjobs <b>Plentymarkets Item Import Stack Update</b> und <b>nicht</b> unverzüglich beginnt. Der Abgleich aller Artkel kann sehr lange dauern und sehr viel Traffic verursachen!';

						Ext.Msg.confirm('Bestätigung erforderlich!', message, function(button)
						{
							if (button === 'yes')
							{
								Ext.Ajax.request({
									url: '{url action=resetImportTimestamp}',
									callback: function(options, success, xhr)
									{
										Shopware.Notification.createGrowlMessage('Aktion ausgeführt', 'Der vollständige Artikelabgleich wurde vorgemerkt');
									},
									jsonData: Ext.encode({
										entity: 'ItemStack'
									})
								});
							}
						});
					}
				}]
			},
                {
                    xtype: 'fieldcontainer',
                    fieldLabel: 'Alle Artikelpakete abgleichen',
                    layout: 'hbox',
                    items: [ {
                    xtype: 'button',
                    text: 'Vormerken',
                    cls: 'secondary small',
                    handler: function () {
                        var message = 'Möchten Sie, dass alle Artikelpakete mit plentymarkets abgeglichen werden?<br><br>Bitte beachten Sie, dass der Abgleich erst mit der Ausführung des Cronjobs <b>Plentymarkets Item Bunde Import</b> und <b>nicht</b> unverzüglich beginnt. Der Abgleich aller Artkel kann sehr lange dauern und sehr viel Traffic verursachen!';

                        Ext.Msg.confirm('Bestätigung erforderlich!', message, function (button) {
                            if (button === 'yes') {
                                Ext.Ajax.request({
                                    url: '{url action=resetImportTimestamp}',
                                    callback: function (options, success, xhr) {
                                        Shopware.Notification.createGrowlMessage('Aktion ausgeführt', 'Der vollständige Artikelpaketabgleich wurde vorgemerkt');
                                    },
                                    jsonData: Ext.encode({
                                        entity: 'ItemBundle'
                                    })
                                });
                            }
                        });
                    }
                }
                    ]
                }
                , {
				xtype: 'fieldcontainer',
				fieldLabel: 'Alle Warenbestände abgleichen',
				layout: 'hbox',
				items: [{
					xtype: 'button',
					text: 'Vormerken',
					cls: 'secondary small',
					handler: function()
					{
						//
						var message = 'Möchten Sie, dass alle Warenbestände mit plentymarkets abgeglichen werden?<br><br>Bitte beachten Sie, dass der Abgleich erst mit der Ausführung des Cronjobs <b>Plentymarkets Item Stock Import</b> und <b>nicht</b> unverzüglich beginnt. Der Abgleich aller Warenbestände kann sehr lange dauern und sehr viel Traffic verursachen!';

						Ext.Msg.confirm('Bestätigung erforderlich!', message, function(button)
						{
							if (button === 'yes')
							{
								Ext.Ajax.request({
									url: '{url action=resetImportTimestamp}',
									callback: function(options, success, xhr)
									{
										Shopware.Notification.createGrowlMessage('Aktion ausgeführt', 'Der vollständige Warenbestandsabgleich wurde vorgemerkt');
									},
									jsonData: Ext.encode({
										entity: 'ItemStock'
									})
								});
							}
						});
					}
				}]
			}

			]
		}, {
			xtype: 'fieldset',
			title: 'Bereinigung',
			defaults: {
				anchor: '100%',
				labelWidth: '33%'
			},
			items: [{
				xtype: 'fieldcontainer',
				fieldLabel: 'Mapping bereinigen',
				layout: 'hbox',
				items: [{
					xtype: 'button',
					text: 'Ausführen',
					cls: 'secondary small',
					handler: function()
					{
						var message = 'Möchten Sie, dass der Cronjob <b>Plentymarkets Cleanup</b> jetzt gestartet wird?';

						Ext.Msg.confirm('Bestätigung erforderlich!', message, function(button)
						{
							if (button === 'yes')
							{
								Ext.Ajax.request({
									url: '{url action=runCleanupAction}',
									callback: function(options, success, xhr)
									{
										Shopware.Notification.createGrowlMessage('Aktion ausgeführt', 'Das Mapping wurde bereinigt');
									},
									jsonData: Ext.encode({
										entity: '1'
									})
								});
							}
						});
					}
				}]
			}, {
				xtype: 'fieldcontainer',
				fieldLabel: 'Merkmale bereinigen',
				layout: 'hbox',
				items: [{
					xtype: 'button',
					text: 'Ausführen',
					cls: 'secondary small',
					handler: function()
					{
						var message = 'Möchten Sie, dass die Eigenschaften bzw. Merkmale bereinigt werden? Folgende Daten werden unwiederbringlich gelöscht:' + '<ul><li>Eigenschaften die nicht gemappt sind</li><li>Eigenschaften die keinem Set zugordnet sind</li><li>Alle Werte die keiner Eigenschaft zugeordnet sind</li></ul>';

						Ext.Msg.confirm('Bestätigung erforderlich!', message, function(button)
						{
							if (button === 'yes')
							{
								Ext.Ajax.request({
									url: '{url action=runCleanupAction}',
									callback: function(options, success, xhr)
									{
										Shopware.Notification.createGrowlMessage('Aktion ausgeführt', 'Die Eigenschaften/Merkmale wurden bereinigt');
									},
									jsonData: Ext.encode({
										entity: '4'
									})
								});
							}
						});
					}
				}]
			}

			]
		}];

		//
		// me.loadRecord(me.settings);
		me.isBuilt = true;

	}

});
// {/block}
