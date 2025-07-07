<?php

use function Laravel\Folio\{name};

name('a-propos');
?>

<x-layouts.marketing>
  <x-container class="py-0">
    <!-- TEAM : BEGIN -->
    <div class="bg-white py-24 sm:py-32">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl sm:text-center">
          <h2 class="text-34l text-balance font-semibold tracking-tight text-gray-900 sm:text-5xl">
            L'équipe
          </h2>
          <p class="mt-6 text-lg/8 text-gray-600">
            Nous sommes un groupe dynamique d'individus passionnés par ce que nous faisons et déterminés à offrir les
            meilleures solutions à nos utilisateurs.
          </p>
        </div>
        <ul role="list"
            class="mx-auto mt-20 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-20 sm:grid-cols-2 lg:max-w-4xl lg:gap-x-8 xl:max-w-none">
          <li class="flex flex-col gap-6 xl:flex-row">
            <img class="aspect-[4/5] w-52 flex-none rounded-2xl object-cover"
                 src="{{ asset('/cywise/img/team-csavelief.jpg') }}"
                 alt=""/>
            <div class="flex-auto">
              <h3 class="text-lg/8 font-semibold tracking-tight text-gray-900">
                Cyrille Savelief
              </h3>
              <p class="text-base/7 text-gray-600">
                Président
              </p>
              <p class="mt-6 text-base/7 text-gray-600">
                Cyrille est le président et cofondateur de Cywise. Il apporte à Cywise plus de 15 ans
                d'expérience dans le domaine de la collecte et du traitement de données.
              </p>
            </div>
          </li>
          <li class="flex flex-col gap-6 xl:flex-row">
            <img class="aspect-[4/5] w-52 flex-none rounded-2xl object-cover"
                 src="{{ asset('/cywise/img/team-pduteil.jpg') }}"
                 alt=""/>
            <div class="flex-auto">
              <h3 class="text-lg/8 font-semibold tracking-tight text-gray-900">
                Pierre Duteil
              </h3>
              <p class="text-base/7 text-gray-600">
                Directeur technique
              </p>
              <p class="mt-6 text-base/7 text-gray-600">
                Pierre est le directeur technique et cofondateur de Cywise. Il apporte à Cywise plus
                de 15 ans d'expérience dans le domaine de la cybersécurité offensive et défensive.
              </p>
            </div>
          </li>
          <li class="flex flex-col gap-6 xl:flex-row">
            <img class="aspect-[4/5] w-52 flex-none rounded-2xl object-cover"
                 src="{{ asset('/cywise/img/team-eesmerian.jpg') }}"
                 alt=""/>
            <div class="flex-auto">
              <h3 class="text-lg/8 font-semibold tracking-tight text-gray-900">
                Eric Esmérian
              </h3>
              <p class="text-base/7 text-gray-600">
                Directeur commercial
              </p>
              <p class="mt-6 text-base/7 text-gray-600">
                Eric est dans le développement de startup depuis 2003. Il a rejoint Cywise en mai et depuis
                organise avec Cyrille et Pierre la version industrielle de l’entreprise.
              </p>
            </div>
          </li>
          <li class="flex flex-col gap-6 xl:flex-row">
            <img class="aspect-[4/5] w-52 flex-none rounded-2xl object-cover"
                 src="{{ asset('/cywise/img/team-pbrisacier.jpg') }}"
                 alt=""/>
            <div class="flex-auto">
              <h3 class="text-lg/8 font-semibold tracking-tight text-gray-900">
                Patrick Brisacier
              </h3>
              <p class="text-base/7 text-gray-600">
                R&D
              </p>
              <p class="mt-6 text-base/7 text-gray-600">
                Patrick est le responsable R&D de Cywise. Avec plus de 20 ans d'expérience, Patrick est un
                expert dans la conception, le déploiement et l’optimisation d’infrastructures complexes.
              </p>
            </div>
          </li>
          <li class="flex flex-col gap-6 xl:flex-row">
            <img class="aspect-[4/5] w-52 flex-none rounded-2xl object-cover"
                 src="{{ asset('/cywise/img/team-jjkhalife.jpg') }}"
                 alt=""/>
            <div class="flex-auto">
              <h3 class="text-lg/8 font-semibold tracking-tight text-gray-900">
                Jean-Jamil Khalifé
              </h3>
              <p class="text-base/7 text-gray-600">
                Expert cybersécurité
              </p>
              <p class="mt-6 text-base/7 text-gray-600">
                Jean apporte son expertise de pentester (audit de sécurité) et de chercheur en sécurité informatique en
                améliorant nos outils et règles de détection.
              </p>
            </div>
          </li>
          <li class="flex flex-col gap-6 xl:flex-row">
            <img class="aspect-[4/5] w-52 flex-none rounded-2xl object-cover"
                 src="{{ asset('/cywise/img/team-bguillot.jpg') }}"
                 alt=""/>
            <div class="flex-auto">
              <h3 class="text-lg/8 font-semibold tracking-tight text-gray-900">
                Bérangère Guillot
              </h3>
              <p class="text-base/7 text-gray-600">
                Customer Success
              </p>
              <p class="mt-6 text-base/7 text-gray-600">
                Bérangère apporte son expertise en relation client en collaborant étroitement avec notre équipe pour
                maximiser les bénéfices que nos clients tirent de nos produits et services.
              </p>
            </div>
          </li>
        </ul>
      </div>
    </div>
    <!-- TEAM : END -->
    <!-- CTA : BEGIN -->
    <div class="bg-white py-24 sm:py-32">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:mx-0 lg:max-w-none">
          <p class="text-base/7 font-semibold text-indigo-600">
            Cywise
          </p>
          <h1 class="mt-2 text-pretty text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">
            Prêt à sécuriser votre activité ?
          </h1>
          <div class="mt-10 grid max-w-xl grid-cols-1 gap-8 text-base/7 text-gray-700 lg:max-w-none lg:grid-cols-2">
            <p>
              Cywise s’installe en quelques clics, détecte vos risques automatiquement, et vous accompagne dans toutes
              vos démarches. Avec toujours cyberbuddy à vos côtés !
            </p>
            <ul class="marker:text-indigo-600 list-inside list-disc" role="list">
              <li>Lancer un audit express</li>
              <li>Générer votre PSSI ou charte informatique</li>
              <li>Déployer un honeypot en deux temps trois mouvements</li>
              <li>Être conforme à NIS2 sans complexité</li>
            </ul>
          </div>
          <div class="mt-10 flex items-center gap-x-6 lg:mt-0 lg:shrink-0">
            <a href="{{ route('tools.cybercheck.init') }}"
               class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
              Démarrer mon audit gratuit !
            </a>
            <a href="{{ route('register') }}" class="text-sm/6 font-semibold text-gray-900 hover:opacity-80">
              Créer un compte
              <span aria-hidden="true">→</span>
            </a>
          </div>
        </div>
      </div>
    </div>
    <!-- CTA : END -->
  </x-container>
</x-layouts.marketing>
