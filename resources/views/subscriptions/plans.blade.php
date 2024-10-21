@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-lg-4 mb-lg-0 mb-4">
      <div class="card shadow-lg">
        <div class="card-header text-center bg-transparent">
          <h3 class="font-weight-bold mt-2 text-center">Essentiel</h3>
          <h1 class="font-weight-bold mt-2">
            100<small class="text-lg me-1">€ (excl. taxes) / mois</small>
          </h1>
        </div>
        <div class="card-body text-lg-start text-center pt-0">
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">5 serveurs</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">15€/mois/serveur supplémentaire</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Scanner de vulnérabilités (100 IP ou DNS)</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Honeypots (HTTP, HTTPS et SSH)</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Évènements de sécurité des serveurs</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Métriques des serveurs</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:red">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M18 6l-12 12"/>
                <path d="M6 6l12 12"/>
              </svg>
            </span>
            <span class="ps-3">PSSI (chatbot)</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:red">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M18 6l-12 12"/>
                <path d="M6 6l12 12"/>
              </svg>
            </span>
            <span class="ps-3">Rapports personnalisés</span>
          </div>
          <a href="{{ route('subscribe', ['plan' => config('towerify.stripe.plans.essential')]) }}"
             class="btn btn-icon btn-outline-danger d-lg-block mt-3 mb-0 select-plan-btn">
            {{ __('Subscribe') }}
            <i class="fas fa-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
    </div>
    <div class="col-lg-4 mb-lg-0 mb-4">
      <div class="card bg-gradient-dark shadow-lg">
        <div class="card-header text-center bg-transparent">
          <h3 class="font-weight-bold mt-2 text-center">Standard</h3>
          <h1 class="font-weight-bold mt-2">
            300<small class="text-lg me-1">€ (excl. taxes) / mois</small>
          </h1>
        </div>
        <div class="card-body text-lg-start text-center pt-0">
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">20 serveurs</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">12€/mois/serveur supplémentaire</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Scanner de vulnérabilités (200 IP ou DNS)</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Honeypots (HTTP, HTTPS et SSH)</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Évènements de sécurité des serveurs</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Métriques des serveurs</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">PSSI (chatbot)</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:red">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M18 6l-12 12"/>
                <path d="M6 6l12 12"/>
              </svg>
            </span>
            <span class="ps-3">Rapports personnalisés</span>
          </div>
          <a href="{{ route('subscribe', ['plan' => config('towerify.stripe.plans.standard')]) }}"
             class="btn btn-icon btn-outline-danger d-lg-block mt-3 mb-0 select-plan-btn">
            {{ __('Subscribe') }}
            <i class="fas fa-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
    </div>
    <div class="col-lg-4 mb-lg-0 mb-4">
      <div class="card shadow-lg">
        <div class="card-header text-center bg-transparent">
          <h3 class="font-weight-bold mt-2 text-center">Premium</h3>
          <h1 class="font-weight-bold mt-2">
            500<small class="text-lg me-1">€ (excl. taxes) / mois</small>
          </h1>
        </div>
        <div class="card-body text-lg-start text-center pt-0">
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">50 serveurs</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">8€/mois/serveur supplémentaire</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Scanner de vulnérabilités (400 IP ou DNS)</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Honeypots (HTTP, HTTPS et SSH)</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Évènements de sécurité des serveurs</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Métriques des serveurs</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">PSSI (chatbot)</span>
          </div>
          <div class="d-flex justify-content-lg-start justify-content-center align-items-center p-1">
            <span style="color:green">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l5 5l10 -10"/>
              </svg>
            </span>
            <span class="ps-3">Rapports personnalisés</span>
          </div>
          <a href="{{ route('subscribe', ['plan' => config('towerify.stripe.plans.premium')]) }}"
             class="btn btn-icon btn-outline-danger d-lg-block mt-3 mb-0 select-plan-btn">
            {{ __('Subscribe') }}
            <i class="fas fa-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
  <div class="row mt-6">
    <div class="col text-center">
      <a href="{{ route('terms') }}">{{ __('Terms') }}</a>
    </div>
  </div>
</div>
@endsection