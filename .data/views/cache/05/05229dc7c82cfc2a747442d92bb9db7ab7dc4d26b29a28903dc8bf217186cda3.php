<?php

/* /partials/footer.html */
class __TwigTemplate_94e89bf0d07ea6753187d488e81beec178d3aa23e2a1c7ddf2b4202fbf182633 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'javascripts' => array($this, 'block_javascripts'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "    <footer>
    \t<div class=\"year\">&copy; ۱۳۹۵</div>
    \tقدرت گرفته <a href=\"http://idprco.ir\">جیم ورک (Jafar Rezaei FrameWork)</a>
    </footer>

    ";
        // line 6
        $this->displayBlock('javascripts', $context, $blocks);
        // line 12
        echo "
  </body>
</html>";
    }

    // line 6
    public function block_javascripts($context, array $blocks = array())
    {
        // line 7
        echo "\t    <script src=\"";
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('asset')->getCallable(), array("js/jquery.min.js")), "html", null, true);
        echo "\"></script>
\t    <script src=\"";
        // line 8
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('asset')->getCallable(), array("js/bootstrap.min.js")), "html", null, true);
        echo "\"></script>
\t    <script src=\"";
        // line 9
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('asset')->getCallable(), array("js/jamework.js")), "html", null, true);
        echo "\"></script>
\t    ";
        // line 10
        echo twig_escape_filter($this->env, (isset($context["extraJs"]) ? $context["extraJs"] : null), "html", null, true);
        echo "
\t";
    }

    public function getTemplateName()
    {
        return "/partials/footer.html";
    }

    public function getDebugInfo()
    {
        return array (  51 => 10,  47 => 9,  43 => 8,  38 => 7,  35 => 6,  29 => 12,  27 => 6,  20 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("    <footer>
    \t<div class=\"year\">&copy; ۱۳۹۵</div>
    \tقدرت گرفته <a href=\"http://idprco.ir\">جیم ورک (Jafar Rezaei FrameWork)</a>
    </footer>

    {% block javascripts %}
\t    <script src=\"{{ asset('js/jquery.min.js') }}\"></script>
\t    <script src=\"{{ asset('js/bootstrap.min.js') }}\"></script>
\t    <script src=\"{{ asset('js/jamework.js') }}\"></script>
\t    {{extraJs}}
\t{% endblock %}

  </body>
</html>", "/partials/footer.html", "/Applications/AMPPS/www/views/partials/footer.html");
    }
}
