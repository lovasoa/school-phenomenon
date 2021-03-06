<?php

class __Mustache_84fa2f612c6e80b8a9e4bbebd90b9455 extends Mustache_Template
{
    private $lambdaHelper;

    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
        $buffer = '';

        $buffer .= $indent . '<!DOCTYPE html>
';
        $buffer .= $indent . '<html lang="fr">
';
        $buffer .= $indent . '	<head>
';
        $buffer .= $indent . '	<meta charset="utf-8">
';
        $buffer .= $indent . '	<meta name="viewport" content="width=device-width, initial-scale=1">
';
        $buffer .= $indent . '	<title>Ajout contact — School Phenomenon</title>
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '	<!-- Bootstrap -->
';
        $buffer .= $indent . '	<link href="css/bootstrap.min.css" rel="stylesheet">
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '	<!-- Custom css -->
';
        $buffer .= $indent . '	<link href="css/devis.css" rel="stylesheet">
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '	<!-- Jura font -->
';
        $buffer .= $indent . '	<link href=\'//fonts.googleapis.com/css?family=Jura&amp;subset=latin,latin-ext\' rel=\'stylesheet\' type=\'text/css\' />
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
';
        $buffer .= $indent . '	<!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
';
        $buffer .= $indent . '	<!--[if lt IE 9]>
';
        $buffer .= $indent . '	  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
';
        $buffer .= $indent . '	  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
';
        $buffer .= $indent . '	<![endif]-->
';
        $buffer .= $indent . '	</head>
';
        $buffer .= $indent . '	<body>
';
        $buffer .= $indent . '	<header class="page-header">
';
        $buffer .= $indent . '		<div class="row">
';
        $buffer .= $indent . '			<div class="col-md-6 col-xs-12">
';
        $buffer .= $indent . '			<h1 class="brand">School Phenomenon <small class="hidden-xs">Excellence <span class="amperstand">&</span> fashion</small></h1>
';
        $buffer .= $indent . '			</div>
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '			<div class="col-md-6 hidden-xs">
';
        $buffer .= $indent . '				<h4>Prêt à porter personnalisable pour les étudiants</h4>
';
        $buffer .= $indent . '			</div>
';
        $buffer .= $indent . '		</div>
';
        $buffer .= $indent . '	</header>
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '	<main class="container">
';
        $buffer .= $indent . '		<h1>Ajout d’un contact au carnet d\'adresses google</h1>
';
        $buffer .= $indent . '
';
        // 'errors' section
        $value = $context->find('errors');
        $buffer .= $this->section7e1417152742ee88585643bbc57a1b4c($context, $indent, $value);
        // 'errors' inverted section
        $value = $context->find('errors');
        if (empty($value)) {
            
            $buffer .= $indent . '		<div class="panel panel-success">
';
            $buffer .= $indent . '		  <p class="panel-heading">Ajout réalisé</p>
';
            $buffer .= $indent . '		  <p class="panel-body">';
            $value = $this->resolveValue($context->find('nom'), $context, $indent);
            $buffer .= htmlspecialchars($value, 2, 'UTF-8');
            $buffer .= ' a été ajouté à vos contacts.</p>
';
            $buffer .= $indent . '		</div>
';
        }
        $buffer .= $indent . '
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '	<p class="well">
';
        // 'authUrl' section
        $value = $context->find('authUrl');
        $buffer .= $this->sectionBfe4d2cb508d55cec087d068b80f3691($context, $indent, $value);
        // 'authUrl' inverted section
        $value = $context->find('authUrl');
        if (empty($value)) {
            
            $buffer .= $indent . '		<a href="?logout" class="btn btn-default">Déconnexion</a>
';
        }
        $buffer .= $indent . '	</p>
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '	</main>
';
        $buffer .= $indent . '
';
        $buffer .= $indent . '	<!-- jQuery (necessary for Bootstrap\'s JavaScript plugins) -->
';
        $buffer .= $indent . '	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
';
        $buffer .= $indent . '	<!-- Include all compiled plugins (below), or include individual files as needed -->
';
        $buffer .= $indent . '	<script src="js/bootstrap.min.js"></script>
';
        $buffer .= $indent . '	</body>
';
        $buffer .= $indent . '</html>
';

        return $buffer;
    }

    private function section7e1417152742ee88585643bbc57a1b4c(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        if (!is_string($value) && is_callable($value)) {
            $source = '
		<div class="panel panel-danger">
		  <p class="panel-heading">{{title}}{{^title}}Erreur{{/title}}</p>
		  <p class="panel-body">{{message}}</p>
		</div>
	';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                $buffer .= $indent . '		<div class="panel panel-danger">
';
                $buffer .= $indent . '		  <p class="panel-heading">';
                $value = $this->resolveValue($context->find('title'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                // 'title' inverted section
                $value = $context->find('title');
                if (empty($value)) {
                    
                    $buffer .= 'Erreur';
                }
                $buffer .= '</p>
';
                $buffer .= $indent . '		  <p class="panel-body">';
                $value = $this->resolveValue($context->find('message'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '</p>
';
                $buffer .= $indent . '		</div>
';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function sectionBfe4d2cb508d55cec087d068b80f3691(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
        if (!is_string($value) && is_callable($value)) {
            $source = '
		<a href="{{authUrl}}" class="btn btn-default">Connectez-vous</a> avec votre compte google pour ajouter {{nom}} à vos contacts google.
		';
            $result = call_user_func($value, $source, $this->lambdaHelper);
            if (strpos($result, '{{') === false) {
                $buffer .= $result;
            } else {
                $buffer .= $this->mustache
                    ->loadLambda((string) $result)
                    ->renderInternal($context);
            }
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                $buffer .= $indent . '		<a href="';
                $value = $this->resolveValue($context->find('authUrl'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= '" class="btn btn-default">Connectez-vous</a> avec votre compte google pour ajouter ';
                $value = $this->resolveValue($context->find('nom'), $context, $indent);
                $buffer .= htmlspecialchars($value, 2, 'UTF-8');
                $buffer .= ' à vos contacts google.
';
                $context->pop();
            }
        }
    
        return $buffer;
    }
}
