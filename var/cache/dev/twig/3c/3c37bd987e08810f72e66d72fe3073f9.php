<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* chatbot/chatbot.html.twig */
class __TwigTemplate_93464c15d1e783d5fea74a5dfa04763d extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'aa' => [$this, 'block_aa'],
            'bb' => [$this, 'block_bb'],
            'cc' => [$this, 'block_cc'],
            'body' => [$this, 'block_body'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 2
        return "base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "chatbot/chatbot.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "chatbot/chatbot.html.twig"));

        $this->parent = $this->load("base.html.twig", 2);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

    }

    // line 4
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_title(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        yield "Chatbot";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    // line 5
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_aa(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "aa"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "aa"));

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    // line 7
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_bb(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "bb"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "bb"));

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    // line 9
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_cc(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "cc"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "cc"));

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    // line 11
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_body(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "body"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "body"));

        // line 12
        yield "<div class=\"container mt-4\">
  

  ";
        // line 15
        if ((array_key_exists("first_name", $context) && array_key_exists("last_name", $context))) {
            // line 16
            yield "  <div class=\"alert alert-success fs-4 fw-bold\">
    👋 Bienvenue <span class=\"text-primary\">";
            // line 17
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::capitalize($this->env->getCharset(), (isset($context["first_name"]) || array_key_exists("first_name", $context) ? $context["first_name"] : (function () { throw new RuntimeError('Variable "first_name" does not exist.', 17, $this->source); })())), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::upper($this->env->getCharset(), (isset($context["last_name"]) || array_key_exists("last_name", $context) ? $context["last_name"] : (function () { throw new RuntimeError('Variable "last_name" does not exist.', 17, $this->source); })())), "html", null, true);
            yield "</span>, vous pouvez poser vos questions à notre assistant RH intelligent.
  </div>
";
        }
        // line 20
        yield "
<div class=\"container mt-4\">
  <h1>Assistant Chatbot</h1>

  <p>
    <a href=\"";
        // line 25
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_user_index");
        yield "\" class=\"btn btn-sm btn-secondary mb-3\">
      <i class=\"fas fa-arrow-left\"></i> Retour
    </a>
  </p>

  ";
        // line 30
        if ((($tmp = (isset($context["error"]) || array_key_exists("error", $context) ? $context["error"] : (function () { throw new RuntimeError('Variable "error" does not exist.', 30, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 31
            yield "    <div class=\"alert alert-danger\">
      <strong>Erreur API :</strong> ";
            // line 32
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["error"]) || array_key_exists("error", $context) ? $context["error"] : (function () { throw new RuntimeError('Variable "error" does not exist.', 32, $this->source); })()), "html", null, true);
            yield "
    </div>
  ";
        }
        // line 35
        yield "
  ";
        // line 36
        if (array_key_exists("history", $context)) {
            // line 37
            yield "  <div class=\"mb-3\">
    ";
            // line 38
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable((isset($context["history"]) || array_key_exists("history", $context) ? $context["history"] : (function () { throw new RuntimeError('Variable "history" does not exist.', 38, $this->source); })()));
            foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                // line 39
                yield "      ";
                if ((CoreExtension::getAttribute($this->env, $this->source, $context["item"], "sender", [], "any", false, false, false, 39) == "user")) {
                    // line 40
                    yield "        <div class=\"alert alert-secondary\"><strong>You:</strong> ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["item"], "message", [], "any", false, false, false, 40), "html", null, true);
                    yield "</div>
      ";
                } else {
                    // line 42
                    yield "        <div class=\"alert alert-info\"><strong>Bot:</strong> ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["item"], "message", [], "any", false, false, false, 42), "html", null, true);
                    yield "</div>
      ";
                }
                // line 44
                yield "    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['item'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 45
            yield "  </div>
";
        }
        // line 47
        yield "

  <form method=\"post\">
    <div class=\"mb-3\">
      <textarea name=\"message\"
                class=\"form-control\"
                rows=\"4\"
                placeholder=\"Posez votre question…\">";
        // line 54
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["message"]) || array_key_exists("message", $context) ? $context["message"] : (function () { throw new RuntimeError('Variable "message" does not exist.', 54, $this->source); })()), "html", null, true);
        yield "</textarea>
    </div>
    <button type=\"submit\" class=\"btn btn-primary\">
      <i class=\"fas fa-paper-plane\"></i> Envoyer
    </button>
  </form>
</div>
";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "chatbot/chatbot.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  259 => 54,  250 => 47,  246 => 45,  240 => 44,  234 => 42,  228 => 40,  225 => 39,  221 => 38,  218 => 37,  216 => 36,  213 => 35,  207 => 32,  204 => 31,  202 => 30,  194 => 25,  187 => 20,  179 => 17,  176 => 16,  174 => 15,  169 => 12,  156 => 11,  134 => 9,  112 => 7,  90 => 5,  67 => 4,  44 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{# templates/produit/chatbot.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}Chatbot{% endblock %}
{%block aa%}
{% endblock %}
{%block bb%}
{% endblock %}
{%block cc%}
{% endblock %}
{% block body %}
<div class=\"container mt-4\">
  

  {% if first_name is defined and last_name is defined %}
  <div class=\"alert alert-success fs-4 fw-bold\">
    👋 Bienvenue <span class=\"text-primary\">{{ first_name|capitalize }} {{ last_name|upper }}</span>, vous pouvez poser vos questions à notre assistant RH intelligent.
  </div>
{% endif %}

<div class=\"container mt-4\">
  <h1>Assistant Chatbot</h1>

  <p>
    <a href=\"{{ path('app_user_index') }}\" class=\"btn btn-sm btn-secondary mb-3\">
      <i class=\"fas fa-arrow-left\"></i> Retour
    </a>
  </p>

  {% if error %}
    <div class=\"alert alert-danger\">
      <strong>Erreur API :</strong> {{ error }}
    </div>
  {% endif %}

  {% if history is defined %}
  <div class=\"mb-3\">
    {% for item in history %}
      {% if item.sender == 'user' %}
        <div class=\"alert alert-secondary\"><strong>You:</strong> {{ item.message }}</div>
      {% else %}
        <div class=\"alert alert-info\"><strong>Bot:</strong> {{ item.message }}</div>
      {% endif %}
    {% endfor %}
  </div>
{% endif %}


  <form method=\"post\">
    <div class=\"mb-3\">
      <textarea name=\"message\"
                class=\"form-control\"
                rows=\"4\"
                placeholder=\"Posez votre question…\">{{ message }}</textarea>
    </div>
    <button type=\"submit\" class=\"btn btn-primary\">
      <i class=\"fas fa-paper-plane\"></i> Envoyer
    </button>
  </form>
</div>
{% endblock %}
", "chatbot/chatbot.html.twig", "C:\\Users\\Motaz\\mm Dropbox\\Motaz Sammoud\\PC\\Desktop\\hahaha\\symfony\\templates\\chatbot\\chatbot.html.twig");
    }
}
