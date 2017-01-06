// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/Main}

/**
 * The main view builds the main tab panel which contains the five tabs: start,
 * api, settings, export, mapping and log. This view also provides a function
 * for setting the tab availability triggered by events. It is extended by the
 * Enlight app window "Enlight.app.Window".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.Main', {
	extend: 'Enlight.app.Window',

	alias: 'widget.plentymarkets-view-main',

	layout: 'fit',
	width: 860,
	height: '90%',
	autoShow: true,

	stateful: true,
	stateId: 'plentymarkets-view-main',

	title: '{s name=main/window/title}plentymarkets{/s}',
	iconCls: 'plenty-p',

	initComponent: function()
	{
		var me = this;
		me.callParent(arguments);
	},

	createTabPanel: function()
	{
		var me = this;

		me.sf = Ext.widget('plentymarkets-view-settings', {
			settings: me.settings,
			main: me
		});
		me.sf.on('activate', me.sf.build);

		me.tabpanel = Ext.create('Ext.tab.Panel', {
			items: [me.sf, {
				xtype: 'plentymarkets-view-mapping-main'
            }, {
				xtype: 'plentymarkets-view-misc'
			}]
		});

		me.add(me.tabpanel);
	}
});
// {/block}
