// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/Start}

/**
 * The settings view builds the graphical elements and loads all saved settings
 * data. It shows for example the chosen warhouse, the producer or the order
 * status. The settings are differentiated into four groups: "Import
 * Artikelstammdaten", "Export Aufträge", "Warenausgang", "Zahlungseingang bei
 * plentymarkets". It is extended by the Ext form panel "Ext.form.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.Start', {

	extend: 'Ext.form.Panel',

	alias: 'widget.plentymarkets-view-start',

	title: '{s name=plentymarkets/view/start/title}Start{/s}',

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

		me.dockedItems = [{
			xtype: 'toolbar',
			cls: 'shopware-toolbar',
			dock: 'bottom',
			ui: 'shopware-ui',
			items: ['->', {
				xtype: 'button',
				cls: 'secondary',
				text: 'Status prüfen',
				handler: function()
				{
					me.fireEvent('checkApi', me)
				}
			}, {
				xtype: 'button',
				cls: 'primary',
				text: 'plentymarkets Administration öffnen'
			}]
		}];

		me.registerEvents();
		me.callParent(arguments);
	},

	init: function()
	{
		var me = this;
		if (me.isBuilt)
		{
			me.fireEvent('checkApi', me)
		}
		else
		{
			me.build();
		}
	},

	/**
	 * Registers additional component events.
	 */
	registerEvents: function()
	{
		this.addEvents('save', 'checkApi');
	},

	build: function()
	{
		var me = this;
		me.add([

		{
			xtype: 'fieldset',
			title: 'API',
			defaults: {
				anchor: '100%',
				labelWidth: '33%'
			},
			items: [{
				xtype: 'displayfield',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiConnectionStatus}Verbindung{/s}',
				name: 'ApiStatus',
				renderer: function(value, x, record)
				{
					if (value == 2)
					{
						return Ext.String.format('<div class="plenty-status plenty-status-ok"> geprüft am ' + Ext.util.Format.date(me.settings.get('ApiLastStatusTimestamp'), 'd.m.Y, H:i:s') + ' Uhr</div>');
					}
					else if (value == 1)
					{
						return Ext.String.format('<div class="plenty-status plenty-status-error"> geprüft am ' + Ext.util.Format.date(me.settings.get('ApiLastStatusTimestamp'), 'd.m.Y, H:i:s') + ' Uhr</div>');
					}
				}
			}, {
				xtype: 'displayfield',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiVersion}Version{/s}',
				value: '110'
			}, {
				xtype: 'displayfield',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiTimestampDeviation}Abweichung{/s}',
				name: 'ApiTimestampDeviation',
				renderer: function(deviation)
				{
					if (me.settings.get('ApiStatus') != 2)
					{
						return Ext.String.format('<div class="plenty-status plenty-status-error"> keine Informationen</div>');
					}
					else if (deviation == 0)
					{
						return Ext.String.format('<div class="plenty-status plenty-status-ok"> keine Abweichung</div>');
					}
					else if (deviation > 0 && deviation < 15)
					{
						return Ext.String.format('<div class="plenty-status plenty-status-api-deviation-ahead"> ' + deviation + ' Sekunde(n)</div>');
					}
					else if (deviation < 0 && deviation > -15)
					{
						return Ext.String.format('<div class="plenty-status plenty-status-api-deviation-behind"> ' + Math.abs(deviation) + ' Sekunde(n)</div>');
					}
					else
					{
						return Ext.String.format('<div class="plenty-status plenty-status-warning"> ' + Math.abs(deviation) + ' Sekunde(n)</div>');
					}
				}
			}]
		}, {
			xtype: 'fieldset',
			title: 'Versionen',
			defaults: {
				anchor: '100%',
				labelWidth: '33%'
			},
			items: [{
				xtype: 'displayfield',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/PlentyVersion}plentymarkets Version{/s}',
				name: 'PlentymarketsVersion'
			}, {
				xtype: 'displayfield',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ConnectorVersion}Connector Version{/s}',
				name: 'ConnectorVersion'
			}]
		}, {
			xtype: 'fieldset',
			title: 'Datenaustausch',
			defaults: {
				anchor: '100%',
				labelWidth: '33%'
			},
			items: [{
				xtype: 'fieldcontainer',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/SettingsStatus}Einstellungen{/s}',
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					name: 'IsSettingsFinished',
					renderer: function(value)
					{
						if (value == "true")
						{
							return Ext.String.format('<div style="height: 16px; width: 16px" class="sprite-tick"></div>');
							return '<b style="color: green">abgeschlossen</b>';
						}
						else
						{
							return Ext.String.format('<div style="height: 16px; width: 16px" class="sprite-cross"></div>')
							return '<b style="color: red">unvollständig</b>';
						}
					}
				}, {
					xtype: 'splitter'
				}, {
					xtype: 'button',
					text: 'Details öffnen',
					cls: 'secondary small',
					handler: function()
					{
						me.main.tabpanel.setActiveTab(2);
					}

				}]
			}, {
				xtype: 'fieldcontainer',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/MappingStatus}Mapping Status{/s}',
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					labelWidth: 300,
					name: 'IsMappingFinished',
					renderer: function(value)
					{
						if (value == "true")
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-tick"></span>');
							return '<b style="color: green">abgeschlossen</b>';
						}
						else
						{
							return Ext.String.format('<div style="height: 16px; width: 16px" class="sprite-cross"></div>')
							return '<b style="color: red">unvollständig</b>';
						}
					}
				}, {
					xtype: 'splitter'
				}, {
					xtype: 'button',
					text: 'Details öffnen',
					cls: 'secondary small',
					handler: function()
					{
						me.main.tabpanel.setActiveTab(4);
					}
				}]
			}, {
				xtype: 'fieldcontainer',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/InitialExportStatus}Datenexport zu plentymarkets{/s}',
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					name: 'IsExportFinished',
					renderer: function(value)
					{
						if (value == "true")
						{
							return Ext.String.format('<div style="height: 16px; width: 16px" class="sprite-tick"></div>');
							return '<b style="color: green">abgeschlossen</b>';
						}
						else
						{
							return Ext.String.format('<div style="height: 16px; width: 16px" class="sprite-cross"></div>')
							return '<b style="color: red">unvollständig</b>';
						}
					}
				}, {
					xtype: 'splitter'
				}, {
					xtype: 'button',
					text: 'Details öffnen',
					cls: 'secondary small',
					handler: function()
					{
						me.main.tabpanel.setActiveTab(3);
					}

				}]
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/MayDatexUser}Datenaustausch mit plentymarkets{/s}',
				allowBlank: true,
				xtype: 'checkbox',
				boxLabel: 'aktivieren',
				name: 'MayDatexUser',
				id: 'MayDatexUser',
				checked: me.settings.get('MayDatexUser'),
				disabled: !me.settings.get('MayDatex'),
				inputValue: true,
				uncheckedValue: false,
				listeners: {
					change: function(x, newValue, oldValue, eOpts)
					{
						me.fireEvent('save', me);
					}
				}
			}]
		}]);

		//
		me.loadRecord(me.settings);
		me.isBuilt = true;

	}

});
// {/block}
