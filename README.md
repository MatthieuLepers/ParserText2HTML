#  **Parse Text to HTML easily with ParseText2HTML**

## **Informations**
Tag Class version 1.0.0
ParserText2HTML Class version 1.0.2

## **Instalation**
You just have to download the class file and include it in you PHP scripts.

`include("ParserText2HTML.class.php");`

## **How to use**
To use the parser you must write your text in an external file.
** /!\ You file must end with a new line /!\ **
You just have to load the file like this:

`$file = "./file2Parse.txt"`

`$parser = new ParserText2HTML($file);`

Parse and get the results

`$html = $parser->parse();`

Parse into an output file

`$parser->parseAsFile('./page.html');`

The parser supports any formats.

You can test a file directly by accessing the php file in URL like:

`http://localhost/myProject/php/ParserText2HTML.class.php?file=parseMe.txt`

## **Syntax**
The parser supports a specific syntax to work correclty.
There is a pattern to respect.

`[tagName]#tagId.class1.class2 customAttr1="value1" customAttr2="value2" [tagContent]`

All after `[tagName]` is optional.
Only custom attributes and tag content need **once** a space **before** declaration.
Check the order of your '#' and '.' for tagId and class, the tagId **is always** before classes.

So, this will not parse:

`h1.class1.class2#myId`

## **Write a tag in a tag**
You need to make a structure like that ?

`<section>`

&nbsp;&nbsp;&nbsp;&nbsp;`<article>`

&nbsp;&nbsp;&nbsp;&nbsp;`</article>`

`</section>`

There is a solution, just add once a tab before you tagName like this:

`section`

&nbsp;`article`

## **More exemples**
See the file exempleSpaces.txt and resultSpaces.html.
resultSpaces.html is the result of parsing the file exempleSpaces.txt using **spaces** for indentation

See the file exempleTabs.txt and resultTabs.html.
resultTabs.html is the result of parsing the file exempleTabs.txt using **tabs** for indentation

## **Supports**
The parser supports all HTML5 tags. If you find a unsupported tag, please make an issue here.
HTML comments are not supported.
Doctype tag is not supported.
Indentations are with spaces **or** tabs

## **Need help ? Just post an issue**
