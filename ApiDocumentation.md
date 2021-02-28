# WebProbe - API documentation

## POST: /api/missionRequest

## Purpose of the API

###Basic structure for the request:
The payload for the request is composed by 4 elements.</br>
- The `url` which is the page were the probe will land and start the preparation.</br>
- The list of `preparation`. A sequence of action the system as to perform.</br>
- The list of `evaluation`. A sequence of element to be found on the final page (after the preparation is executed) and the instructions to idenfy them.</br>
- The `resultType` if each element of the evaluation is expected to be single or a list.

```json
{
	"data": {
		"url": "http://www.example.com/index.php",
		"preparation": [
		... //list of preparation objects
		],
		"evaluation": [
		... //list of evaluation objects
		],
		"resultType": "single",
		"noCache": false
	}
}
```

<table>
	<tr style="background-color:#bababa;">
		<td>field </td>
		<td>type</td>
		<td>mandatory</td>
		<td>possible values</td>
		<td>description</td>
	</tr>
	<tr>
		<td>url</td>
		<td>string</td>
		<td>true</td>
		<td>any string</td>
		<td>the URL of the page to reach to start perorming preparations (if present) or evaluations (if present)</td>
	</tr>
	<tr>
		<td>preparation</td>
		<td>list of objects</td>
		<td>false</td>
		<td>preparation</td>
		<td>list of preparation objects</td>
	</tr>
	<tr>
		<td>evaluation</td>
		<td>list of objects</td>
		<td>false</td>
		<td>evaluation</td>
		<td>list of evaluation objects</td>
	</tr>
	<tr>
		<td>resultType</td>
		<td>string</td>
		<td>true</td>
		<td>["single","all"]</td>
		<td>if one set of evaluation objects has to be returned or all the found ones</td>
	</tr>
	<tr>
		<td>noCache</td>
		<td>bool</td>
		<td>false</td>
		<td>true, false</td>
		<td>if true will overwrite the cache (available only for super users)</td>
	</tr>
</table>

###Preparation objects:

Preparation objects are a list of actions the system needs to perform in order to reach the page or status wanted, where the evaluation can be executed.</br>
Preparations are executed in sequence from the top to the bottom of the list.</br>
In case there is no preparation needed, the `preparation` element can be either empty or not defined.


```json
"preparation": [
	{
		"identifier": "example_id",
        "attribute": "id",
        "action": "click",
        "value": null,
        "repeat": 1
	}
]
```

<table>
	<tr style="background-color:#bababa;">
		<td>field </td>
		<td>type</td>
		<td>mandatory</td>
		<td>possible values</td>
		<td>description</td>
	</tr>
	<tr>
		<td>identifier</td>
		<td>string</td>
		<td>true</td>
		<td>any string</td>
		<td>unique indentifier of the element against to performing the action.</td>
	</tr>
	<tr>
		<td>attribute</td>
		<td>string</td>
		<td>true</td>
		<td>["id","name"]</td>
		<td>the type of attribute of the identifier</td>
	</tr>
	<tr>
		<td>action</td>
		<td>string</td>
		<td>true</td>
		<td>["click","set","submit"]</td>
		<td>the action to be performed against the identifier</td>
	</tr>
	<tr>
		<td>value</td>
		<td>string</td>
		<td>false (true only if the action is "set")</td>
		<td>any string</td>
		<td>the value to set the identifier to if the action is "set"</td>
	</tr>
	<tr>
		<td>repeat</td>
		<td>integer</td>
		<td>false</td>
		<td>any integer</td>
		<td>the number of times this given preparation has to be repeated (by default if not defined is: 1)</td>
	</tr>
</table>

### Evaluation objects:
Evaluation objects are a list of elements to be found and returned from the target URL after all the preparation (if present) are executed.

```json
"evaluation: [
	"productName": {
		"type": "tag",
		"tagType": "span",
		"attribute": "id",
		"identifier": "product_name_id"
	},
	"productType": {
		"type": "domxquery",
       "query": "//div[@id='product_type']//span"
	}
]
```

<table>
	<tr style="background-color:#bababa;">
		<td>field </td>
		<td>type</td>
		<td>mandatory</td>
		<td>possible values</td>
		<td>description</td>
	</tr>
	<tr>
<td>(key)</td>
		<td>string</td>
		<td>true</td>
		<td>any string</td>
		<td>Identification name of the field to evaluate. The same key name will be used as return name.</td>
	</tr>
	<tr>
		<td>type</td>
		<td>string</td>
		<td>true</td>
		<td>["tag","text","domxquery"]</td>
		<td>The type of object to find.</td>
	</tr>
</table>

This set of fieleds for the evaluation objects is extended by a set of dedicated fields depending on the value of "type".


### field definition for evaluation type: tag
<table>
	<tr style="background-color:#bababa;">
		<td>field </td>
		<td>type</td>
		<td>mandatory</td>
		<td>possible values</td>
		<td>description</td>
	</tr>
	<tr>
		<td>tagType</td>
		<td>string</td>
		<td>true</td>
		<td>any string</td>
		<td>The kind of tag to be identified</td>
	</tr>
	<tr>
		<td>attribute</td>
		<td>string</td>
		<td>true</td>
		<td>any string</td>
		<td>the kind of identifier for the html tag element.</td>
	</tr>
	<tr>
		<td>identifier</td>
		<td>string</td>
		<td>true</td>
		<td>any string</td>
		<td>the identifier of the attribute</td>
	</tr>
</table>

> **Note:** it the identifier of the evaluation of type "tag" is ending with a wildcard symbol `*` this is translated into the partial identification of the tag. E.g. the idenfier of the tagType `span` with attribute `id` and idenfifier `product_id_*` will result in a query returning the contents of all the `span` with `id` starting with `product_id_`.

### field definition for evaluation type: text
<table>
	<tr style="background-color:#bababa;">
		<td>field </td>
		<td>type</td>
		<td>mandatory</td>
		<td>possible values</td>
		<td>description</td>
	</tr>
	<tr>
		<td>identifier</td>
		<td>string</td>
		<td>true</td>
		<td>any string</td>
		<td>the idenfifier where the portion of text starts</td>
	</tr>
	<tr>
		<td>closeIdentifier</td>
		<td>string</td>
		<td>true</td>
		<td>any string</td>
		<td>the identifier where the portion of text ends</td>
	</tr>
</table>

### field definition for evaluation type: domxquery
<table>
	<tr style="background-color:#bababa;">
		<td>field </td>
		<td>type</td>
		<td>mandatory</td>
		<td>possible values</td>
		<td>description</td>
	</tr>
	<tr>
		<td>query</td>
		<td>string</td>
		<td>true</td>
		<td>any string</td>
		<td>The domxquery to be executed</td>
	</tr>
	<tr>
		<td>node</td>
		<td>integer</td>
		<td>false</td>
		<td>any string</td>
		<td>Which node of the domxquery results to return. If undefined, all the nodes will be returned. </td>
	</tr>
	<tr>
		<td>optional</td>
		<td>boolean</td>
		<td>false</td>
		<td>[true,false]</td>
		<td>It true, the result will not be returned if the element is not found</td>
	</tr>
</table>


###Basic structure for the response:
The response is a json object containing all the result found for the evaluation.
In case of `resultType ` `single` one field is returned for each evaluation.
In case of `resultType ` `all` all the elements found are returned for each evaluation element.

```json
{
    "data": [
            {
                "name": "productName",
                "value": "lorem ipsum"
            },
            {
                "name": "productType",
                "value": "dolorem sit amet"
            },
        ]
   }
```




