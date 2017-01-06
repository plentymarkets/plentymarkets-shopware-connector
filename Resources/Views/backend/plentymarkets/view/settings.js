// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/Settings}

/**
 * The settings view builds the graphical elements and loads all saved settings
 * data. It shows for example the chosen warehouse, the manufacturer or the order
 * status. The settings are differentiated into four groups: "Import
 * Artikelstammdaten", "Export Aufträge", "Warenausgang", "Zahlungseingang bei
 * plentymarkets". It is extended by the Ext form panel "Ext.form.Panel".
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.Settings', {

    extend: 'Ext.form.Panel',

    alias: 'widget.plentymarkets-view-settings',

    title: '{s name=plentymarkets/view/settings/title}Einstellungen{/s}',

    autoScroll: true,

    cls: 'shopware-form',

    layout: 'anchor',

    border: false,

    isBuilt: false,

    stores: {},

    defaults: {
        anchor: '100%',
        margin: 10
    },

    initComponent: function () {
        var me = this;

        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function () {
        this.addEvents('save');
        this.addEvents('test');
    },

    build: function () {
        var me = this;
        if (me.isBuilt == true) {
            return;
        }
        me.setLoading(true);
        me.add(me.getFieldSets());
        me.addDocked(me.createToolbar());
        me.loadRecord(me.settings);
        me.isBuilt = true;
        me.setLoading(false);
    },

    /**
     * Creates the grid toolbar for the favorite grid
     *
     * @return Ext.toolbar.Toolbar
     */
    createToolbar: function () {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            cls: 'shopware-toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            items: ['->', {
                xtype: 'button',
                text: '{s name=plentymarkets/view/settings/button/test}Zugangsdaten Testen{/s}',
                cls: 'secondary',
                handler: function () {
                    me.fireEvent('test', me);
                }
            }, {
                xtype: 'button',
                text: '{s name=plentymarkets/view/settings/button/save}Speichern{/s}',
                cls: 'primary',
                handler: function () {
                    me.fireEvent('save', me);
                }
            }]
        });
    },

    getFieldSets: function () {
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
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiUrl}URL{/s}',
                        helpText: 'Die URL muss mit <b>http://</b> oder <b>https://</b> beginnen.',
                        supportText: 'Tragen Sie hier die URL Ihres plentymarkets-Systems ein. Sie finden diese Information in der plentymarkets-Administration unter <b>Einstellungen » Grundeinstellungen » API-Daten » Host</b>.',
                        emptyText: 'http://www.ihr-plentymarkets-system.de/',
                        name: 'ApiUrl',
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
            }
        ];
    }

});
// {/block}
