# Billingo plugin for Craft Commerce

This plugin will connect Craft Commerce with Billingo: whenever an Order is paid, a new invoice will be generated in Billingo. Once a Refund is made (even if it is a partial or a full refund) a new invoice will be generated while the previous one will be stornoed. 

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later, and Craft Commerce 3.2.0 or later. 

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require webmenedzser/billingo

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Billingo for Craft Commerce.

## Billingo for Craft Commerce Overview

This plugin will connect Craft Commerce with Billingo: whenever an Order is paid, a new invoice will be generated in Billingo. Once a Refund is made (even if it is a partial or a full refund) a new invoice will be generated while the previous one will be stornoed. 

## Features:
- New invoice is generated when a successful payment is made. 
- On a partial refund the previous invoice is stornoed and a new one is generated with the new price.
- On a full refund the invoice will stornoed. 
- If a target volume is set, the plugin will automatically download the newly generated invoice there.  
- Normal and Proform Invoices supported. 
- Configurable settings:
    - Invoice Type: Normal / Proform
    - Invoice Template Languages: hu, en, de, fr, hr, it, ro, slo
    - Electronic Invoice: yes / no
    - Ask Billingo to send e-mails to clients
    - VAT options: 0%, 5%, 18%, 27%
    - Rounding options: 0, 1, 5, 10
    - Block ID
    - Unit Type string
    - Invoice Volume
- Support for Project Config

## Configuring Billingo for Craft Commerce

Navigate to **Settings 🡒 Billingo for Craft Commerce**, or copy `vendor/webmenedzser/billingo/src/config.php` to `config/billingo.php` and edit it to your needs. 

## Found a bug?

Check the issues or [open a new one](https://github.com/webmenedzser/billingo-for-craft-commerce/issues)! 

Brought to you by [dr. Ottó Radics](https://www.webmenedzser.hu)
