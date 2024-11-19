<div id="block-note"></div>
<script>
  window.addEventListener('load', function () {
    const settings = {
      initialContent: [{
        type: "qa_block", props: {
          questions: ["Quel est le nom de l'entité?", "Quel est le type d'entité (société, collectivité, etc.)?"],
          answers: [],
        }
      }, /* {
        type: "ai_block", props: {
          prompt: 'CyberBuddy',
          collection: "Quel est le nom de l'entité?",
          collections: ["Quel est le nom de l'entité?"],
          text: null,
        },
      }, {
        type: "ai_block", props: {
          prompt: 'CyberBuddy',
          collection: "Quel est le type d'entité (société, collectivité, etc.)?",
          collections: ["Quel est le type d'entité (société, collectivité, etc.)?"],
          text: null,
        },
      }, {
        type: "heading", props: {level: 1}, content: "Chart informatique",
      }, {
        type: "heading",
        props: {level: 2},
        content: "Dispositions applicables aux utilisateur du système d'information de <NOM_DE_L'ENTITE>",
      }, {
        type: "paragraph",
        content: "Les différents outils technologiques utilisés offrent aux utilisateurs de <NOM_DE_L'ENTITE> une grande ouverture vers l’extérieur. Cette ouverture peut apporter des améliorations de performances importantes si l’utilisation de ces outils technologiques est faite à bon escient et selon certaines règles. L’usage des moyens numériques mis à disposition doit permettre de préserver le système d’information, le bon fonctionnement des services et les droits et libertés de chacun."
      },*/],
    };
    window.BlockNote.render("block-note", settings);
  });
</script>