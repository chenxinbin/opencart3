<?php

/* default/template/extension/module/weibo_login.twig */
class __TwigTemplate_6306c7029fdd3ed59802773c5f32926eb59a2997c41f88e4e5ffbbb5855b14f6 extends Twig_Template
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
        if ((isset($context["logged"]) ? $context["logged"] : null)) {
            echo " 

";
        } elseif (        // line 3
(isset($context["weibo_login_authorized"]) ? $context["weibo_login_authorized"] : null)) {
            echo " 

";
        } elseif (        // line 5
(isset($context["is_weixin"]) ? $context["is_weixin"] : null)) {
            echo " 

";
        } else {
            // line 7
            echo "   
<div class=\"list-group\">
\t<a href=\"";
            // line 9
            echo (isset($context["code_url"]) ? $context["code_url"] : null);
            echo "\" class=\"list-group-item\"><img src=\"catalog/view/theme/default/image/weibo_login.png\" title=\"";
            echo (isset($context["text_weibo_login"]) ? $context["text_weibo_login"] : null);
            echo "\" alt=\"";
            echo (isset($context["text_weibo_login"]) ? $context["text_weibo_login"] : null);
            echo "\" border=\"0\" /></a>
      
</div>
";
        }
        // line 12
        echo " ";
    }

    public function getTemplateName()
    {
        return "default/template/extension/module/weibo_login.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  50 => 12,  39 => 9,  35 => 7,  29 => 5,  24 => 3,  19 => 1,);
    }
}
/* {% if logged %} */
/* */
/* {% elseif weibo_login_authorized %} */
/* */
/* {% elseif is_weixin %} */
/* */
/* {% else %}   */
/* <div class="list-group">*/
/* 	<a href="{{ code_url }}" class="list-group-item"><img src="catalog/view/theme/default/image/weibo_login.png" title="{{ text_weibo_login }}" alt="{{ text_weibo_login }}" border="0" /></a>*/
/*       */
/* </div>*/
/* {% endif %} */
