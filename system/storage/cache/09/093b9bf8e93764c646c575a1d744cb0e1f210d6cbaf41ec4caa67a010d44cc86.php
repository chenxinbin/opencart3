<?php

/* install/step_4.twig */
class __TwigTemplate_1701208f75933f6eb516d6882c647629f2a5d464209bf9704e27eb51c07d2457 extends Twig_Template
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
        echo (isset($context["header"]) ? $context["header"] : null);
        echo "
<div class=\"container\">
  <header>
    <div class=\"row\">
      <div class=\"col-sm-6\">
        <h1 class=\"pull-left\">4<small>/4</small></h1>
        <h3>";
        // line 7
        echo (isset($context["heading_title"]) ? $context["heading_title"] : null);
        echo "<br>
          <small>";
        // line 8
        echo (isset($context["text_step_4"]) ? $context["text_step_4"] : null);
        echo "</small></h3>
      </div>
      <div class=\"col-sm-6\">
        <div id=\"logo\" class=\"pull-right hidden-xs\"><img src=\"view/image/logo.png\" alt=\"MyCnCart\" title=\"MyCnCart\" /></div>
      </div>
    </div>
  </header>
  ";
        // line 15
        if ((isset($context["success"]) ? $context["success"] : null)) {
            // line 16
            echo "  <div class=\"alert alert-success alert-dismissible\">";
            echo (isset($context["success"]) ? $context["success"] : null);
            echo "</div>
  ";
        }
        // line 18
        echo "  <div class=\"alert alert-danger alert-dismissible\"><i class=\"fa fa-exclamation-circle\"></i> ";
        echo (isset($context["error_warning"]) ? $context["error_warning"] : null);
        echo "
    <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
  </div>
  <div class=\"visit\">
    <div class=\"row\">
      <div class=\"col-sm-5 col-sm-offset-1 text-center\">
        <p><i class=\"fa fa-shopping-cart fa-5x\"></i></p>
        <a href=\"../\" class=\"btn btn-secondary\">";
        // line 25
        echo (isset($context["text_catalog"]) ? $context["text_catalog"] : null);
        echo "</a></div>
      <div class=\"col-sm-5 text-center\">
        <p><i class=\"fa fa-cog fa-5x white\"></i></p>
        <a href=\"../admin/\" class=\"btn btn-secondary\">";
        // line 28
        echo (isset($context["text_admin"]) ? $context["text_admin"] : null);
        echo "</a></div>
    </div>
  </div>
  <div class=\"modules\">
    <div class=\"row\">
      <div class=\"col-sm-12 text-center\"><a href=\"http://www.mycncart.com\" target=\"_BLANK\" class=\"btn btn-default\">";
        // line 33
        echo (isset($context["text_extension"]) ? $context["text_extension"] : null);
        echo "</a></div>
    </div>
  </div>
  <div class=\"mailing\">
    <div class=\"row\">
      <div class=\"col-sm-12\"><i class=\"fa fa-envelope-o fa-5x\"></i>
        <h3>";
        // line 39
        echo (isset($context["text_mail"]) ? $context["text_mail"] : null);
        echo "<br>
          <small>";
        // line 40
        echo (isset($context["text_mail_description"]) ? $context["text_mail_description"] : null);
        echo "</small></h3>
        <a href=\"http://www.mycncart.com\" target=\"_BLANK\" class=\"btn btn-secondary\">";
        // line 41
        echo (isset($context["button_mail"]) ? $context["button_mail"] : null);
        echo "</a></div>
    </div>
  </div>

  <div class=\"support text-center\">
    <div class=\"row\">
      <div class=\"col-sm-4\"><a href=\"http://www.weibo.com/mycncart\" class=\"icon transition\"><i class=\"fa fa-weibo fa-4x\"></i></a>
        <h3>";
        // line 48
        echo (isset($context["text_weibo"]) ? $context["text_weibo"] : null);
        echo "</h3>
        <p>";
        // line 49
        echo (isset($context["text_weibo_description"]) ? $context["text_weibo_description"] : null);
        echo "</p>
        <a href=\"http://www.weibo.com/mycncart\">";
        // line 50
        echo (isset($context["text_weibo_visit"]) ? $context["text_weibo_visit"] : null);
        echo "</a></div>
      <div class=\"col-sm-4\"><a href=\"http://www.mycncart.cn\" class=\"icon transition\"><i class=\"fa fa-comments fa-4x\"></i></a>
        <h3>";
        // line 52
        echo (isset($context["text_forum"]) ? $context["text_forum"] : null);
        echo "</h3>
        <p>";
        // line 53
        echo (isset($context["text_forum_description"]) ? $context["text_forum_description"] : null);
        echo "</p>
        <a href=\"http://www.mycncart.cn\">";
        // line 54
        echo (isset($context["text_forum_visit"]) ? $context["text_forum_visit"] : null);
        echo "</a></div>
      <div class=\"col-sm-4\"><a href=\"http://www.mycncart.com\" class=\"icon transition\"><i class=\"fa fa-user fa-4x\"></i></a>
        <h3>";
        // line 56
        echo (isset($context["text_commercial"]) ? $context["text_commercial"] : null);
        echo "</h3>
        <p>";
        // line 57
        echo (isset($context["text_commercial_description"]) ? $context["text_commercial_description"] : null);
        echo "</p>
        <a href=\"http://www.mycncart.com\" target=\"_BLANK\">";
        // line 58
        echo (isset($context["text_commercial_visit"]) ? $context["text_commercial_visit"] : null);
        echo "</a></div>
    </div>
  </div>
</div>
";
        // line 62
        echo (isset($context["footer"]) ? $context["footer"] : null);
        echo "
<script type=\"text/javascript\"><!--
\$(document).ready(function() {
\t\$.ajax({
\t\turl: '";
        // line 66
        echo (isset($context["extension"]) ? $context["extension"] : null);
        echo "',
\t\ttype: 'post',
\t\tdataType: 'json',
\t\tsuccess: function(json) {
\t\t\tif (json['extensions']) {
\t\t\t\thtml  = '';

\t\t\t\tfor (i = 0; i < json['extensions'].length; i++) {
\t\t\t\t\textension = json['extensions'][i];

\t\t\t\t\thtml += '<div class=\"col-sm-6 module\">';
\t\t\t\t\thtml += '  <a class=\"thumbnail pull-left\" href=\"' + extension['href'] + '\"><img src=\"' + extension['image'] + '\" alt=\"' + extension['name'] + '\" /></a>';
\t\t\t\t\thtml += '  <h5>' + extension['name'] + '</h5>';
\t\t\t\t\thtml += '  <p>' + extension['price'] + ' <a target=\"_BLANK\" href=\"' + extension['href'] + '\">";
        // line 79
        echo (isset($context["text_view"]) ? $context["text_view"] : null);
        echo "</a></p>';
\t\t\t\t\thtml += '  <div class=\"clearfix\"></div>';
\t\t\t\t\thtml += '</div>';

\t\t\t\t\ti++;
\t\t\t\t}

\t\t\t\t\$('#extension').html(html);
\t\t\t} else {
\t\t\t\t\$('#extension').fadeOut();
\t\t\t}
\t\t}
\t});
});
//--></script>
";
    }

    public function getTemplateName()
    {
        return "install/step_4.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  166 => 79,  150 => 66,  143 => 62,  136 => 58,  132 => 57,  128 => 56,  123 => 54,  119 => 53,  115 => 52,  110 => 50,  106 => 49,  102 => 48,  92 => 41,  88 => 40,  84 => 39,  75 => 33,  67 => 28,  61 => 25,  50 => 18,  44 => 16,  42 => 15,  32 => 8,  28 => 7,  19 => 1,);
    }
}
/* {{ header }}*/
/* <div class="container">*/
/*   <header>*/
/*     <div class="row">*/
/*       <div class="col-sm-6">*/
/*         <h1 class="pull-left">4<small>/4</small></h1>*/
/*         <h3>{{ heading_title }}<br>*/
/*           <small>{{ text_step_4 }}</small></h3>*/
/*       </div>*/
/*       <div class="col-sm-6">*/
/*         <div id="logo" class="pull-right hidden-xs"><img src="view/image/logo.png" alt="MyCnCart" title="MyCnCart" /></div>*/
/*       </div>*/
/*     </div>*/
/*   </header>*/
/*   {% if success %}*/
/*   <div class="alert alert-success alert-dismissible">{{ success }}</div>*/
/*   {% endif %}*/
/*   <div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> {{ error_warning }}*/
/*     <button type="button" class="close" data-dismiss="alert">&times;</button>*/
/*   </div>*/
/*   <div class="visit">*/
/*     <div class="row">*/
/*       <div class="col-sm-5 col-sm-offset-1 text-center">*/
/*         <p><i class="fa fa-shopping-cart fa-5x"></i></p>*/
/*         <a href="../" class="btn btn-secondary">{{ text_catalog }}</a></div>*/
/*       <div class="col-sm-5 text-center">*/
/*         <p><i class="fa fa-cog fa-5x white"></i></p>*/
/*         <a href="../admin/" class="btn btn-secondary">{{ text_admin }}</a></div>*/
/*     </div>*/
/*   </div>*/
/*   <div class="modules">*/
/*     <div class="row">*/
/*       <div class="col-sm-12 text-center"><a href="http://www.mycncart.com" target="_BLANK" class="btn btn-default">{{ text_extension }}</a></div>*/
/*     </div>*/
/*   </div>*/
/*   <div class="mailing">*/
/*     <div class="row">*/
/*       <div class="col-sm-12"><i class="fa fa-envelope-o fa-5x"></i>*/
/*         <h3>{{ text_mail }}<br>*/
/*           <small>{{ text_mail_description }}</small></h3>*/
/*         <a href="http://www.mycncart.com" target="_BLANK" class="btn btn-secondary">{{ button_mail }}</a></div>*/
/*     </div>*/
/*   </div>*/
/* */
/*   <div class="support text-center">*/
/*     <div class="row">*/
/*       <div class="col-sm-4"><a href="http://www.weibo.com/mycncart" class="icon transition"><i class="fa fa-weibo fa-4x"></i></a>*/
/*         <h3>{{ text_weibo }}</h3>*/
/*         <p>{{ text_weibo_description }}</p>*/
/*         <a href="http://www.weibo.com/mycncart">{{ text_weibo_visit }}</a></div>*/
/*       <div class="col-sm-4"><a href="http://www.mycncart.cn" class="icon transition"><i class="fa fa-comments fa-4x"></i></a>*/
/*         <h3>{{ text_forum }}</h3>*/
/*         <p>{{ text_forum_description }}</p>*/
/*         <a href="http://www.mycncart.cn">{{ text_forum_visit }}</a></div>*/
/*       <div class="col-sm-4"><a href="http://www.mycncart.com" class="icon transition"><i class="fa fa-user fa-4x"></i></a>*/
/*         <h3>{{ text_commercial }}</h3>*/
/*         <p>{{ text_commercial_description }}</p>*/
/*         <a href="http://www.mycncart.com" target="_BLANK">{{ text_commercial_visit }}</a></div>*/
/*     </div>*/
/*   </div>*/
/* </div>*/
/* {{ footer }}*/
/* <script type="text/javascript"><!--*/
/* $(document).ready(function() {*/
/* 	$.ajax({*/
/* 		url: '{{ extension }}',*/
/* 		type: 'post',*/
/* 		dataType: 'json',*/
/* 		success: function(json) {*/
/* 			if (json['extensions']) {*/
/* 				html  = '';*/
/* */
/* 				for (i = 0; i < json['extensions'].length; i++) {*/
/* 					extension = json['extensions'][i];*/
/* */
/* 					html += '<div class="col-sm-6 module">';*/
/* 					html += '  <a class="thumbnail pull-left" href="' + extension['href'] + '"><img src="' + extension['image'] + '" alt="' + extension['name'] + '" /></a>';*/
/* 					html += '  <h5>' + extension['name'] + '</h5>';*/
/* 					html += '  <p>' + extension['price'] + ' <a target="_BLANK" href="' + extension['href'] + '">{{ text_view }}</a></p>';*/
/* 					html += '  <div class="clearfix"></div>';*/
/* 					html += '</div>';*/
/* */
/* 					i++;*/
/* 				}*/
/* */
/* 				$('#extension').html(html);*/
/* 			} else {*/
/* 				$('#extension').fadeOut();*/
/* 			}*/
/* 		}*/
/* 	});*/
/* });*/
/* //--></script>*/
/* */
