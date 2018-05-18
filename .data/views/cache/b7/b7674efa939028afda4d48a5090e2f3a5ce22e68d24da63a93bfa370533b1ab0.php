<?php

/* home/index.html */
class __TwigTemplate_43f63ef3a12c5ae46733c8a024bf3136a52aa03a562a802a2a0fabd6e6a19997 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo twig_include($this->env, $context, "/partials/header.html");
        echo "
\t
\t<div class=\"panel panel-success\">
    \t<div class=\"panel-heading\">لیست اعضا</div>
    \t<div class=\"panel-body\">
\t    ";
        // line 6
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["users"]) ? $context["users"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["user"]) {
            // line 7
            echo "\t\t\t";
            echo twig_escape_filter($this->env, $this->getAttribute($context["user"], "uid", array(), "array"), "html", null, true);
            echo " : ";
            echo twig_escape_filter($this->env, $this->getAttribute($context["user"], "name", array(), "array"), "html", null, true);
            echo "
\t    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['user'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 9
        echo "\t    </div>
    </div>

";
        // line 12
        echo twig_include($this->env, $context, "/partials/footer.html");
    }

    public function getTemplateName()
    {
        return "home/index.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  47 => 12,  42 => 9,  31 => 7,  27 => 6,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("{{ include('/partials/header.html')}}
\t
\t<div class=\"panel panel-success\">
    \t<div class=\"panel-heading\">لیست اعضا</div>
    \t<div class=\"panel-body\">
\t    {%for user in  users %}
\t\t\t{{user['uid']}} : {{user['name']}}
\t    {% endfor %}
\t    </div>
    </div>

{{ include('/partials/footer.html')}}", "home/index.html", "/Applications/AMPPS/www/views/home/index.html");
    }
}
