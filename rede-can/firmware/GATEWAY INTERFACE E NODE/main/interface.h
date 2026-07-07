#ifndef INTERFACE_H
#define INTERFACE_H

#include <stdio.h>
#include <string.h>
#include <stdlib.h>

const char* generate_html_page(
    const char* dummy_status,
    const char* dummy,
    int adc_value,
    float velocidade,
    int valor_slider_local, 
    int valor_node_red_freq 
)
{
    // ==================================================
    // HTML HEADER & CSS
    // ==================================================
    const char* html_header =
    "<!DOCTYPE html>"
    "<html lang='pt-BR'>"
    "<head>"
    "<meta charset='UTF-8'>"
    "<meta name='viewport' content='width=device-width, initial-scale=1.0'>"
    "<title>MONITOR CAN</title>"
    "<style>"
    "* { margin:0; padding:0; box-sizing:border-box; }"
    "body { font-family:Arial,sans-serif; background:#0f172a; color:white; min-height:100vh; }"
    ".header { padding:30px; text-align:center; background:linear-gradient(90deg,#2563eb,#06b6d4); box-shadow:0 4px 20px rgba(0,0,0,0.4); }"
    ".header h1 { font-size:42px; letter-spacing:2px; margin-bottom:10px; }"
    ".header p { font-size:16px; opacity:0.9; }"
    ".container { display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:25px; padding:30px; }"
    ".card { background:#1e293b; border-radius:22px; padding:30px; box-shadow:0 8px 25px rgba(0,0,0,0.35); transition:0.3s; border:1px solid rgba(255,255,255,0.05); }"
    ".card:hover { transform:translateY(-6px); }"
    ".title { font-size:15px; text-transform:uppercase; letter-spacing:1px; color:#94a3b8; margin-bottom:20px; }"
    ".value { font-size:48px; font-weight:bold; margin-bottom:30px; }"
    ".unit { font-size:24px; opacity:0.8; }"
    ".online { color:#22c55e; font-size:32px; font-weight:bold; }"
    
    ".btn-group { display:flex; gap:15px; margin-top:15px; }"
    ".btn { flex:1; padding:18px; border:none; border-radius:16px; color:white; font-size:18px; font-weight:bold; cursor:pointer; transition:0.3s; text-transform:uppercase; }"
    ".btn-ligar { background:#16a34a; }"
    ".btn-ligar:hover { transform:scale(1.04); background:#15803d; }"
    ".btn-desligar { background:#dc2626; }"
    ".btn-desligar:hover { transform:scale(1.04); background:#b91c1c; }"
    
    /* Card MQTT */
    ".btn-aquecer { background:#ea580c; }"
    ".btn-aquecer:hover { transform:scale(1.04); background:#c2410c; }"
    ".btn-refrigerar { background:#2563eb; }"
    ".btn-refrigerar:hover { transform:scale(1.04); background:#1d4ed8; }"
    
    /* Estilos Adicionados para Exibição dos Dados do MQTT */
    ".mqtt-display-box { background:rgba(167,139,250,0.08); padding:15px; border-radius:12px; border:1px dashed rgba(167,139,250,0.3); margin-top:20px; }"
    ".mqtt-data-row { display:flex; justify-content:space-between; margin:8px 0; font-size:15px; color:#e2e8f0; }"
    ".status-badge { font-weight:bold; color:#cbd5e1; transition:0.2s; }"

    ".slider-container { background:rgba(0,0,0,0.2); padding:15px; border-radius:12px; margin-bottom:20px; border:1px solid rgba(255,255,255,0.02); }"
    ".slider-label { display:flex; justify-content:space-between; font-size:14px; color:#cbd5e1; margin-bottom:8px; text-transform:uppercase; }"
    ".slider-input { width:100%; accent-color:#06b6d4; cursor:pointer; height:6px; border-radius:3px; }" 
    ".nr-display-box { background:rgba(6,182,212,0.1); padding:15px; border-radius:12px; border:1px dashed #06b6d4; margin-bottom:20px; text-align:center; }"
    ".footer { text-align:center; padding:20px; color:#94a3b8; font-size:14px; margin-top:10px; }"
    "</style>"
    "</head>"
    "<body>"
    "<div class='header'>"
    "<h1>MONITOR CAN</h1>"
    "<p>GATEWAY</p>"
    "</div>"
    "<div class='container'>";

    // ==================================================
    // HTML FOOTER & JAVASCRIPT
    // ==================================================
    const char* html_footer =
    "</div>"
    "<div class='footer'>ESP32 • HTTP • Node-RED • IFSC</div>"
    "<script>"
    "let userIsDragging = false;"
    
    "async function atualizarDados(){"
    "try{"
    "const resposta = await fetch('/data');"
    "const dados = await resposta.json();"
    
    "if(dados.velocidade !== undefined){"
    "document.getElementById('speed').innerHTML = dados.velocidade.toFixed(1) + ' <span class=\"unit\">km/h</span>';"
    "}"
    
    "if(!userIsDragging && dados.slider_local !== undefined){"
    "document.getElementById('slider_display').innerText = dados.slider_local;"
    "document.getElementById('freqSlider').value = dados.slider_local;"
    "}"
    
    "if(dados.nr_freq !== undefined){"
    "document.getElementById('nr_value_display').innerText = dados.nr_freq;"
    "}"
    
    /* ATUALIZAÇÃO EM TEMPO REAL: Dados de Temperatura e Status Simulado */
    "if(dados.mqtt_temp !== undefined){"
    "document.getElementById('mqtt_temp_display').innerText = dados.mqtt_temp.toFixed(1);"
    "}"
    "if(dados.mqtt_status !== undefined){"
    "const statusEl = document.getElementById('mqtt_status_display');"
    "statusEl.innerText = dados.mqtt_status;"
    "if(dados.mqtt_status.includes('Resfriando')) statusEl.style.color = '#38bdf8';"
    "else if(dados.mqtt_status.includes('Aquecendo')) statusEl.style.color = '#f87171';"
    "else if(dados.mqtt_status.includes('Estável')) statusEl.style.color = '#4ade80';"
    "else statusEl.style.color = '#cbd5e1';"
    "}"
    
    "}catch(e){ console.log(e); }"
    "}"

    "async function enviarSliderLocal(valor){"
    "userIsDragging = false;"
    "document.getElementById('slider_display').innerText = valor;"
    "try{"
    "await fetch('/set_slider', {"
    "method: 'POST',"
    "headers: {'Content-Type': 'text/plain'},"
    "body: valor"
    "});"
    "}catch(e){ console.log('Erro ao enviar slider local:', e); }"
    "}"

    "async function acionarLigar(){"
    "try{ await fetch('/ligar', { method: 'POST' }); }catch(e){}"
    "}"

    "async function acionarDesligar(){"
    "try{ await fetch('/desligar', { method: 'POST' }); }catch(e){}"
    "}"

    /*FUNÇÕES JAVASCRIPT COM O NODE-RED */
    "async function acionarMqttAquecer(){"
    "try{ await fetch('/mqtt_aquecer', { method: 'POST' }); }catch(e){}"
    "}"

    "async function acionarMqttResfriar(){"
    "try{ await fetch('/mqtt_resfriar', { method: 'POST' }); }catch(e){}"
    "}"

    "async function acionarMqttDesligar(){"
    "try{ await fetch('/mqtt_desligar', { method: 'POST' }); }catch(e){}"
    "}"
    
    "atualizarDados();"
    "setInterval(atualizarDados,500);"
    "</script>"
    "</body>"
    "</html>";

    // ==================================================
    // MONTA HTML 
    // ==================================================
    size_t html_size = strlen(html_header) + strlen(html_footer) + 8500;
    char* html_page = (char*)malloc(html_size);
    if (html_page == NULL) return NULL;

    snprintf(
        html_page, html_size,
        "%s" 

        /* CARD 1: VELOCIDADE DO VEÍCULO (REDE CAN) */
        "<div class='card'>"
        "<div class='title'>VELOCIDADE ATUAL</div>"
        "<div class='value' id='speed'>%.1f <span class='unit'>km/h</span></div>"
        "<div class='title'>MONITORAMENTO REDE CAN</div>"
        "<div class='online'>ONLINE</div>"
        "<p style='margin-top:18px;font-size:16px;color:#cbd5e1;line-height:1.5;'>"
        "Barramento CAN operacional a 250 Kbps. Recebendo ID 0x4D2."
        "</p>"
        "</div>"
        
        /* CARD 2: BLOCO PROFINET */
        "<div class='card'>"
        "<div class='title' style='font-size:22px;color:white;'>PROFINET - CLP</div>"
        
        "<div class='slider-container'>"
        "<div class='slider-label'>"
        "<span>Ajuste Local</span>"
        "<span><strong id='slider_display' style='color:#06b6d4; font-size:18px;'>%d</strong> Hz</span>"
        "</div>"
        "<input type='range' id='freqSlider' class='slider-input' min='0' max='60' value='%d' "
        "oninput='userIsDragging=true; document.getElementById(\"slider_display\").innerText=this.value;' "
        "onchange='enviarSliderLocal(this.value)'>"
        "</div>"

        "<div class='nr-display-box'>"
        "<div style='font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:1px; margin-bottom:5px;'>Referência Freq. Node-RED</div>"
        "<div style='font-size:32px; font-weight:bold; color:#06b6d4;'><span id='nr_value_display'>%d</span> <span style='font-size:18px;'>Hz</span></div>"
        "</div>"

        "<p style='font-size:15px;color:#cbd5e1;line-height:1.5;margin-bottom:5px;'>"
        "Controle de Operação do CLP:"
        "</p>"
        
        "<div class='btn-group'>"
        "<button class='btn btn-ligar' onclick='acionarLigar()'>LIGAR</button>"
        "<button class='btn btn-desligar' onclick='acionarDesligar()'>DESLIGAR</button>"
        "</div>"
        "</div>"

        /* CARD 3 ATUALIZADO: Com exibição de temperatura*/
        "<div class='card' style='border: 1px solid rgba(167, 139, 250, 0.2);'>"
        "<div class='title' style='font-size:22px; color:#a78bfa;'>MQTT - ESP</div>"
        "<p style='font-size:14px; color:#cbd5e1; line-height:1.5; margin-bottom:20px;'>"
        "Controle de operação da rede MQTT e simulação."
        "</p>"
        "<div class='btn-group' style='flex-direction: column; gap: 10px;'>"
        "<button class='btn btn-aquecer' onclick='acionarMqttAquecer()'>AQUECER</button>"
        "<button class='btn btn-refrigerar' onclick='acionarMqttResfriar()'>REFRIGERAR</button>"
        "<button class='btn btn-desligar' onclick='acionarMqttDesligar()'>DESLIGAR</button>"
        "</div>"
        
        /*CAMPOS EXIBIDOS NO CARD MQTT */
        "<div class='mqtt-display-box'>"
        "<div class='mqtt-data-row'>"
        "<span>Temperatura:</span>"
        "<span><strong id='mqtt_temp_display' style='color:#a78bfa;'>0.0</strong> °C</span>"
        "</div>"
        "<div class='mqtt-data-row'>"
        "<span>Status Atual:</span>"
        "<span id='mqtt_status_display' class='status-badge'>Aguardando</span>"
        "</div>"
        "</div>"
        "</div>"
        
        "%s", 
        html_header, velocidade, valor_slider_local, valor_slider_local, valor_node_red_freq, html_footer
    );

    return html_page;
}

#endif