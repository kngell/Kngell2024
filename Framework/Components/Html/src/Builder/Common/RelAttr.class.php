<?php

declare(strict_types=1);

enum RelAttr : string
{
    case DEFAULT = '';
    case EXTERNAL = 'external'; //Specifies that the referenced document is not a part of the current site
    case HELP = 'help';	//Links to a help document
    case LICENSE = 'license';	//Links to copyright information for the document
    case NEXT = 'next';	//The next document in a selection
    case NOFILLOW = 'nofollow';	//Links to an unendorsed document, like a paid link.
    //("nofollow" is used by Google, to specify that the Google search spider should not follow that link)
    case NOOPENER = 'noopener';
    case NOREFERRER = 'noreferrer';	//Specifies that the browser should not send a HTTP referrer header if the user follows the hyperlink
    case OPENER = 'opener';
    case PREV = 'prev';	//The previous document in a selection
    case SEARCH = 'search';	//Links to a search tool for the document
}