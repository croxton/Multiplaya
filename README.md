#Multiplaya

* Author: [Mark Croxton](http://hallmark-design.co.uk/)

## Version 1.0.0 
#### Note: this is still somewhat experimental

* Requires: [ExpressionEngine 2](http://expressionengine.com/)
* [Playa](http://pixelandtonic.com/playa)

## Description

Extends the Playa module to allow retrieval of parent/child relationships for multiple entry ids. Specifically, you can add multiple entry ids to the entry_id parameter.

## Installation

1. Copy the multiplaya folder to ./system/expressionengine/third_party/
2. In the CP, navigate to Add-ons > Modules and click the 'Install' link for the Multiplaya module

## Examples

Get related entries which have ALL the parents listed - use '&' as a delimiter

	{exp:multiplaya:parents entry_id="20&31&73"}
	...
	{/exp:multiplaya:parents}

Get related entries which have ANY of the parents listed - use '|' as a delimiter

	{exp:multiplaya:parents entry_id="20|31|73"}
	...
	{/exp:multiplaya:parents}

Works in the same way with the other standalone Playa tags.

