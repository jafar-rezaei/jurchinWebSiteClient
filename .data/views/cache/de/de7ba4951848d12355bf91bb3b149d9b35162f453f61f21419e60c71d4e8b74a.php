<?php

/* /partials/header.html */
class __TwigTemplate_e1fe7e6c5143e0b06d266e5b57aec960c2ffed8f5a59e64616e9894740cc8047 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'stylesheets' => array($this, 'block_stylesheets'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html lang=\"en\">
\t<head>
\t\t<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">
\t\t<meta charset=\"utf-8\">
\t\t<title>";
        // line 6
        echo twig_escape_filter($this->env, (isset($context["pageTitle"]) ? $context["pageTitle"] : null), "html", null, true);
        echo "</title>
\t\t<meta name=\"generator\" content=\"Jamework [Jafar Rezaei FrameWork 1.2]\" />
\t\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\">
\t\t<!--[if lt IE 9]>
\t\t\t<script src=\"//html5shim.googlecode.com/svn/trunk/html5.js\"></script>
\t\t<![endif]-->

\t    ";
        // line 13
        $this->displayBlock('stylesheets', $context, $blocks);
        // line 20
        echo "\t</head>
\t<body>

\t<div class=\"container\">

      <nav class=\"navbar navbar-inverse\" role=\"navigation\">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class=\"navbar-header\">
          <button type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\".navbar-ex1-collapse\">
            <span class=\"sr-only\">T</span>
            <span class=\"icon-bar\"></span>
            <span class=\"icon-bar\"></span>
            <span class=\"icon-bar\"></span>
          </button>
          <a class=\"navbar-brand\" href=\"#\"><h1 style=\"font-size: inherit;font-family: inherit;margin: inherit;padding: 0;\">مسابقه فناورد </h1></a>
        </div>
      
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class=\"collapse navbar-collapse navbar-ex1-collapse\">

          <ul class=\"nav navbar-nav navbar-left\">
            <li><a href=\"#\">";
        // line 41
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, "now", "d/m/Y H:i", "Asia/Tehran"), "html", null, true);
        echo "</a></li>
          </ul>
          <ul class=\"nav navbar-nav\">
            <li class=\"active\"><a href=\"#\">خانه</a></li>
            <li><a href=\"#\">ورود به مدیریت</a></li>
            <li><a href=\"#\">تماس با ما</a></li>
            <li class=\"dropdown\">
              <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">دسته ها <b class=\"caret\"></b></a>
              <ul class=\"dropdown-menu\">
                <li><a href=\"#\">فعالیت</a></li>
                <li><a href=\"#\">برنامه ریزی ها</a></li>
              </ul>
            </li>
          </ul>
        </div><!-- /.navbar-collapse -->
      </nav>


\t\t<h2 class=\"siteTop\">";
        // line 59
        echo twig_escape_filter($this->env, (isset($context["pageTitle"]) ? $context["pageTitle"] : null), "html", null, true);
        echo "</h1>
\t\t<p class=\"lead\">";
        // line 60
        echo twig_escape_filter($this->env, (isset($context["pageDes"]) ? $context["pageDes"] : null), "html", null, true);
        echo "</p>


";
    }

    // line 13
    public function block_stylesheets($context, array $blocks = array())
    {
        // line 14
        echo "\t        <link href=\"";
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('asset')->getCallable(), array("css/bootstrap.min.css")), "html", null, true);
        echo "\" rel=\"stylesheet\" />
\t        <link href=\"";
        // line 15
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('asset')->getCallable(), array("css/styles.css")), "html", null, true);
        echo "\" rel=\"stylesheet\" />
\t        <link href=\"";
        // line 16
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('asset')->getCallable(), array("css/fonts/font-awesome/css/font-awesome.css")), "html", null, true);
        echo "\" rel=\"stylesheet\" />
\t        <link href=\"";
        // line 17
        echo twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('asset')->getCallable(), array("css/fonts.css")), "html", null, true);
        echo "\" rel=\"stylesheet\" />
\t        ";
        // line 18
        echo twig_escape_filter($this->env, (isset($context["extraCss"]) ? $context["extraCss"] : null), "html", null, true);
        echo "
\t    ";
    }

    public function getTemplateName()
    {
        return "/partials/header.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  115 => 18,  111 => 17,  107 => 16,  103 => 15,  98 => 14,  95 => 13,  87 => 60,  83 => 59,  62 => 41,  39 => 20,  37 => 13,  27 => 6,  20 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("<!DOCTYPE html>
<html lang=\"en\">
\t<head>
\t\t<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">
\t\t<meta charset=\"utf-8\">
\t\t<title>{{ pageTitle }}</title>
\t\t<meta name=\"generator\" content=\"Jamework [Jafar Rezaei FrameWork 1.2]\" />
\t\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\">
\t\t<!--[if lt IE 9]>
\t\t\t<script src=\"//html5shim.googlecode.com/svn/trunk/html5.js\"></script>
\t\t<![endif]-->

\t    {% block stylesheets %}
\t        <link href=\"{{ asset('css/bootstrap.min.css') }}\" rel=\"stylesheet\" />
\t        <link href=\"{{ asset('css/styles.css') }}\" rel=\"stylesheet\" />
\t        <link href=\"{{ asset('css/fonts/font-awesome/css/font-awesome.css') }}\" rel=\"stylesheet\" />
\t        <link href=\"{{ asset('css/fonts.css') }}\" rel=\"stylesheet\" />
\t        {{extraCss}}
\t    {% endblock %}
\t</head>
\t<body>

\t<div class=\"container\">

      <nav class=\"navbar navbar-inverse\" role=\"navigation\">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class=\"navbar-header\">
          <button type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\".navbar-ex1-collapse\">
            <span class=\"sr-only\">T</span>
            <span class=\"icon-bar\"></span>
            <span class=\"icon-bar\"></span>
            <span class=\"icon-bar\"></span>
          </button>
          <a class=\"navbar-brand\" href=\"#\"><h1 style=\"font-size: inherit;font-family: inherit;margin: inherit;padding: 0;\">مسابقه فناورد </h1></a>
        </div>
      
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class=\"collapse navbar-collapse navbar-ex1-collapse\">

          <ul class=\"nav navbar-nav navbar-left\">
            <li><a href=\"#\">{{ \"now\"|date('d/m/Y H:i', timezone=\"Asia/Tehran\") }}</a></li>
          </ul>
          <ul class=\"nav navbar-nav\">
            <li class=\"active\"><a href=\"#\">خانه</a></li>
            <li><a href=\"#\">ورود به مدیریت</a></li>
            <li><a href=\"#\">تماس با ما</a></li>
            <li class=\"dropdown\">
              <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">دسته ها <b class=\"caret\"></b></a>
              <ul class=\"dropdown-menu\">
                <li><a href=\"#\">فعالیت</a></li>
                <li><a href=\"#\">برنامه ریزی ها</a></li>
              </ul>
            </li>
          </ul>
        </div><!-- /.navbar-collapse -->
      </nav>


\t\t<h2 class=\"siteTop\">{{pageTitle}}</h1>
\t\t<p class=\"lead\">{{ pageDes}}</p>


", "/partials/header.html", "/Applications/AMPPS/www/views/partials/header.html");
    }
}
