
</main>

    <div class="footer">
        <div class="footer-content">
            <span class="footer-text">© 2025 SIOSeG - Todos os direitos reservados</span>
            <span class="footer-separator">•</span>
            <a href="javascript:void(0)" onclick="openPrivacyPolicy(); return false;" class="footer-link">Política de Privacidade</a>
        </div>
    </div>

    <!-- Scripts globais -->
    <script src="<?= $base_url ?>assets/js/autocomplete.js?v=<?= time(); ?>"></script>
    <script src="<?= $base_url ?>assets/js/lgpd-privacy.js?v=<?= time(); ?>"></script>

    <!-- Fallback para CDNs offline -->
    <script>
    // Verifica se Font Awesome carregou, senão usa fallback local
    if (!window.FontAwesome) {
        console.warn('Font Awesome CDN falhou, usando fallback local se disponível');
    }
    
    // Verifica se Google Fonts carregou
    if (!document.fonts || document.fonts.size === 0) {
        console.warn('Google Fonts pode não ter carregado corretamente');
    }
    </script>

    <!-- Modal global para visualizar avaliação (presente em todas as páginas) -->
    <div id="evaluation-modal" class="modal">
        <div class="modal-content">
            <span id="evaluation-close" class="modal-close">&times;</span>
            <h3>Avaliação da OS <span id="evaluation-os-id"></span></h3>
            <div id="evaluation-body">
                <p><strong>Nota:</strong> <span id="evaluation-note">-</span></p>
                <p><strong>Comentário:</strong></p>
                <div id="evaluation-comment" style="white-space:pre-wrap;">-</div>
            </div>
        </div>
    </div>

    <!-- Modal Política de Privacidade LGPD -->
    <div id="privacyModal" class="privacy-modal">
        <div class="privacy-modal-content">
            <div class="privacy-modal-header">
                <h2><i class="fas fa-shield-alt"></i> Política de Privacidade - LGPD</h2>
                <span class="privacy-close" onclick="closePrivacyPolicy()">&times;</span>
            </div>
            <div class="privacy-modal-body">
                <h3>1. Princípios do Tratamento (Art. 6º LGPD)</h3>
                <p>Seguimos os princípios estabelecidos pela LGPD:</p>
                <ul>
                    <li><strong>Finalidade:</strong> Dados coletados para propósitos específicos do sistema de OS</li>
                    <li><strong>Adequação:</strong> Tratamento compatível com as finalidades informadas</li>
                    <li><strong>Necessidade:</strong> Limitação ao mínimo necessário para as finalidades</li>
                    <li><strong>Transparência:</strong> Informações claras sobre o tratamento</li>
                </ul>

                <h3>2. Base Legal (Art. 7º LGPD)</h3>
                <p>O tratamento dos dados pessoais é realizado com base em:</p>
                <ul>
                    <li><strong>Inciso I:</strong> Consentimento do titular (clientes)</li>
                    <li><strong>Inciso II:</strong> Cumprimento de obrigação legal (funcionários/técnicos)</li>
                    <li><strong>Inciso V:</strong> Execução de contrato (prestação de serviços)</li>
                    <li><strong>Inciso VI:</strong> Legítimo interesse (gestão operacional)</li>
                </ul>

                <h3>3. Dados Coletados</h3>
                <p>Coletamos apenas dados necessários conforme Art. 6º, III (necessidade):</p>
                <ul>
                    <li>Dados de identificação (nome, CPF, RG, email)</li>
                    <li>Dados de contato (telefone, endereço)</li>
                    <li>Dados de acesso (email e senha hash)</li>
                    <li>Dados operacionais (ordens de serviço, avaliações)</li>
                </ul>

                <h3>4. Direitos do Titular (Art. 18º LGPD)</h3>
                <p>Conforme a LGPD, você possui os seguintes direitos:</p>
                <ul>
                    <li><strong>Inciso I:</strong> Confirmação da existência de tratamento</li>
                    <li><strong>Inciso II:</strong> Acesso aos dados</li>
                    <li><strong>Inciso III:</strong> Correção de dados incompletos, inexatos ou desatualizados</li>
                    <li><strong>Inciso IV:</strong> Anonimização, bloqueio ou eliminação</li>
                    <li><strong>Inciso V:</strong> Portabilidade dos dados</li>
                    <li><strong>Inciso VI:</strong> Eliminação dos dados tratados com consentimento</li>
                    <li><strong>Inciso VIII:</strong> Revogação do consentimento</li>
                </ul>

                <h3>5. Segurança (Art. 46º LGPD)</h3>
                <p>Implementamos medidas técnicas e administrativas conforme Art. 46º:</p>
                <ul>
                    <li>Criptografia de senhas (hash bcrypt)</li>
                    <li>Controle de acesso por perfis</li>
                    <li>Logs de auditoria</li>
                    <li>Backup seguro dos dados</li>
                </ul>

                <h3>6. Responsabilidades (Art. 41º e 42º LGPD)</h3>
                <p>Como controlador dos dados, assumimos as responsabilidades do Art. 41º, adotando medidas eficazes para demonstrar cumprimento das normas de proteção de dados.</p>

                <h3>7. Comunicação de Incidentes (Art. 48º LGPD)</h3>
                <p>Em caso de incidente de segurança, comunicaremos à ANPD e aos titulares conforme estabelecido no Art. 48º da LGPD.</p>

                <h3>8. Exercício de Direitos</h3>
                <p>Para exercer qualquer direito previsto no Art. 18º da LGPD ou esclarecer dúvidas sobre o tratamento de dados pessoais, entre em contato através dos canais disponíveis no sistema.</p>
            </div>
            <div class="privacy-modal-footer">
                <button onclick="closePrivacyPolicy()" class="privacy-btn">Entendi</button>
            </div>
        </div>
    </div>

</body>
