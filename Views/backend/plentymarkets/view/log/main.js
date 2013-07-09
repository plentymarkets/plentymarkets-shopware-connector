// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/log/Main}
Ext.define('Shopware.apps.Plentymarkets.view.log.Main', {

	extend: 'Ext.tab.Panel',

	alias: 'widget.plentymarkets-view-log-main',

	title: '{s name=plentymarkets/view/mappingtabs/titlex}Log{/s}',

	autoScroll: true,

	layout: 'anchor',

	border: false,

	initComponent: function()
	{
		var me = this;

		me.items = [{
			xtype: 'plentymarkets-view-log-grid',
			title: 'Alles',
			type: 0
		}, {
			xtype: 'plentymarkets-view-log-grid',
			title: 'Nur Fehler',
			type: 1
		}, {
			xtype: 'plentymarkets-view-log-grid',
			title: 'Nur Meldungen',
			type: 2
		}];

		me.callParent(arguments);
	},

});
// {/block}
