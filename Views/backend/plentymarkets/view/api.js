// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/Api}

/**
 * The api view builds the graphical elements like labels and textfields. It
 * shows the three rows "URL", "Benutzername" and "Passwort". It is extended by
 * the Ext form panel "Ext.form.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.Api', {

	extend: 'Ext.form.Panel',

	alias: 'widget.plentymarkets-view-api',

	title: '{s name=plentymarkets/view/settings/titlex}API{/s}',

	autoScroll: true,

	cls: 'shopware-form',

	layout: 'anchor',

	border: false,

	isBuilt: false,

	defaults: {
		anchor: '100%',
		margin: 10
	},

	initComponent: function()
	{
		var me = this;
		me.dockedItems = [me.createToolbar()];
		me.registerEvents();
		me.callParent(arguments);
	},

	/**
	 * Registers additional component events.
	 */
	registerEvents: function()
	{
		this.addEvents('save');
	},

	build: function()
	{
		var me = this;

		if (!me.isBuilt)
		{
			me.add(me.getFieldSets());
			me.isBuilt = true;
		}

		me.loadRecord(me.settings);
	},

	createToolbar: function()
	{
		var me = this;

		return Ext.create('Ext.toolbar.Toolbar', {
			cls: 'shopware-toolbar',
			dock: 'bottom',
			ui: 'shopware-ui',
			items: ['->', me.createTestButton(), me.createSaveButton()]
		});
	},

	createSaveButton: function()
	{
		var me = this;

		return Ext.create('Ext.button.Button', {
			text: '{s name=plentymarkets/view/settings/button/save}Speichern{/s}',
			cls: 'primary',
			iconCls: 'plenty-save',
			handler: function()
			{
				me.fireEvent('save', me);
			}
		})
	},

	createTestButton: function()
	{
		var me = this;

		return Ext.create('Ext.button.Button', {
			text: '{s name=plentymarkets/view/settings/button/test}Zugangsdaten testen{/s}',
			cls: 'secondary',
			handler: function()
			{
				me.fireEvent('test', me);
			}
		})
	},

	getFieldSets: function()
	{
		var me = this;

		return [

		{
			xtype: 'fieldset',
			title: 'Zugangsdaten',
			layout: 'anchor',
			defaults: {
				labelWidth: 155,
				anchor: '100%'
            },
			items: [

			{
				xtype: 'textfield',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiWsdl}URL{/s}',
				helpText: 'Die URL muss mit <b>http://</b> oder <b>https://</b> beginnen.',
				supportText: 'Tragen Sie hier die URL Ihres plentymarkets-Systems ein. Sie finden diese Information in der plentymarkets-Administration unter <b>Einstellungen » Grundeinstellungen » API-Daten » Host</b>.',
				emptyText: 'http://www.ihr-plentymarkets-system.de/',
				name: 'ApiWsdl',
				allowBlank: false
            }, {
				xtype: 'textfield',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiUsernamex}Benutzername{/s}',
				supportText: 'Der Benutzer sollte vom Typ <b>API</b> sein und nur für shopware verwendert werden. Achtung: Der Benutzer wird in Ihrem plentymarkets System unter <b>Einstellungen » Grundeinstellungen » Benutzer » Konten</b> angelegt!',
				name: 'ApiUsername',
				allowBlank: false
			}, {
				xtype: 'textfield',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiPasswordx}Passwort{/s}',
				supportText: 'Bitte vergeben Sie ein sicheres und starkes Passwort.',
				name: 'ApiPassword',
				allowBlank: false,
				inputType: 'password'
			}]
		},

		{
			xtype: 'fieldset',
			title: 'Erweiterte Einstellungen',
			layout: 'anchor',
			defaults: {
				labelWidth: 155,
				anchor: '100%'
            },
			items: [{
				xtype: 'checkbox',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiIgnoreGetServerTime}GetServerTime nicht loggen{/s}',
				supportText: 'Aktivieren, wenn der SOAP Call GetServerTime nicht geloggt werden soll.',
				name: 'ApiIgnoreGetServerTime',
				inputValue: true,
				uncheckedValue: false
            }, {
				xtype: 'checkbox',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiHideCallsInLog}Soap Calls im Log ausblenden{/s}',
				supportText: 'Aktivieren, wenn die SOAP Calls im Log ausgeblendet werden sollen.',
				name: 'ApiHideCallsInLog',
				inputValue: true,
				uncheckedValue: false
            }, {
				xtype: 'checkbox',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiUseGzipCompression}Anfragen komprimieren{/s}',
				supportText: 'Aktivieren, wenn die SOAP Responses gzip komprimiert empfangen werden können.',
				name: 'ApiUseGzipCompression',
				inputValue: true,
				uncheckedValue: false
            }, {
				xtype: 'checkbox',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiLogHttpHeaders}Header Loggen{/s}',
				supportText: 'Aktivieren, wenn die SOAP HTTP Header ins Log geschrieben werden sollen. Nur für das Debugging notwendig!',
				name: 'ApiLogHttpHeaders',
				inputValue: true,
				uncheckedValue: false
            }]
		}];
	}

});
// {/block}
