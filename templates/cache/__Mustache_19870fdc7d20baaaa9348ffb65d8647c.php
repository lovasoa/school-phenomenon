<?php

class __Mustache_19870fdc7d20baaaa9348ffb65d8647c extends Mustache_Template
{
    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $buffer = '';

        $buffer .= $indent . '<atom:entry xmlns:atom=\'http://www.w3.org/2005/Atom\'
';
        $buffer .= $indent . '        xmlns:gd=\'http://schemas.google.com/g/2005\'
';
        $buffer .= $indent . '        xmlns:gContact=\'http://schemas.google.com/contact/2008\'>
';
        $buffer .= $indent . '      <atom:category scheme=\'http://schemas.google.com/g/2005#kind\'
';
        $buffer .= $indent . '        term=\'http://schemas.google.com/contact/2008#contact\'/>
';
        $buffer .= $indent . '	<gd:name>
';
        $buffer .= $indent . '		<gd:givenName>';
        $value = $this->resolveValue($context->find('prenom'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '</gd:givenName>
';
        $buffer .= $indent . '		<gd:familyName>';
        $value = $this->resolveValue($context->find('nom'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '</gd:familyName>
';
        $buffer .= $indent . '	</gd:name>
';
        $buffer .= $indent . '	<gd:organization>
';
        $buffer .= $indent . '		<gd:orgName>';
        $value = $this->resolveValue($context->find('association'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '</gd:orgName>
';
        $buffer .= $indent . '		<gd:orgTitle>Responsable sweats</gd:orgTitle>
';
        $buffer .= $indent . '	</gd:organization>
';
        $buffer .= $indent . '	<atom:content type=\'text\'>Contact ajouté automatiquement par le site de devis de School Phenomenon, créé par Ophir LOJKINE.</atom:content>
';
        $buffer .= $indent . '	<gd:email rel=\'http://schemas.google.com/g/2005#work\' primary=\'true\' address="';
        $value = $this->resolveValue($context->findDot('email}" />
	<gd:phoneNumber rel=\'http://schemas.google.com/g/2005#work\' primary=\'true\'>
		{{telephone'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '
';
        $buffer .= $indent . '	</gd:phoneNumber>
';
        $buffer .= $indent . '	<gd:structuredPostalAddress rel=\'http://schemas.google.com/g/2005#work\' primary=\'true\'>
';
        $buffer .= $indent . '		<gd:city>';
        $value = $this->resolveValue($context->find('ville'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '</gd:city>
';
        $buffer .= $indent . '		<gd:street>';
        $value = $this->resolveValue($context->find('adresse'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '</gd:street>
';
        $buffer .= $indent . '		<gd:postcode>';
        $value = $this->resolveValue($context->find('code-postal'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '</gd:postcode>
';
        $buffer .= $indent . '		<gd:country>France</gd:country>
';
        $buffer .= $indent . '	</gd:structuredPostalAddress>
';
        $buffer .= $indent . '	<gContact:groupMembershipInfo deleted=\'false\' href=\'';
        $value = $this->resolveValue($context->find('gcontact-group-id'), $context, $indent);
        $buffer .= htmlspecialchars($value, 2, 'UTF-8');
        $buffer .= '\'/>
';
        $buffer .= $indent . '	</atom:entry>
';

        return $buffer;
    }
}
