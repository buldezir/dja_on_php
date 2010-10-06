<?php
// Shortcuts //
/**
 *
 * @return Dja_Config
 */
function CONFIG()
{
    return Dja_App::getInstance()->getConfig();
}

/**
 *
 * @return Dja_App
 */
function APP()
{
    return Dja_App::getInstance();
}

/**
 *
 * @param $tpl
 * @param array $vars
 * @return Dja_Response
 */
function renderToResponse($tpl, array $vars = array())
{
    return new Dja_Response(Dja_Template::getInstance()->render($tpl, $vars));
}