# Investigação de problemas — Parte 5

## Cenário

"O cliente informou que a integração de veículos parou de funcionar desde ontem."

## Informações que eu pediria ao cliente

1. O que exatamente aconteceu? Apareceu alguma mensagem de erro, demorou para carregar ou salvou alguma informação incorreta?
2. Se houver, pode enviar um print da tela ou a mensagem de erro? (facilita encontrar a origem do problema)
3. Desde quando parou? Consegue lembrar mais ou menos o horário?
4. O problema acontece com todos os usuários/veículos ou apenas com alguns específicos?
5. Mudou alguma coisa do lado de vocês desde ontem?

## O que eu verificaria primeiro (logs da aplicação)

Começo pelos logs porque é a evidência mais barata e já aponta a direção. O tipo de erro no momento da falha estreita as hipóteses: 401/403 indica problema de credencial; timeout ou connection refused indica API externa ou rede; erro de conexão ou query indica banco; 500 indica código. A partir disso, sigo para onde o log apontar, em vez de checar tudo em paralelo.

## API externa

Se a integração usa uma API externa, verifico se o serviço está no ar. Começando pela página de status do fornecedor, se houver. Testo a API fora da aplicação (Postman, Insomnia ou curl), ou até mesmo diretamente, para confirmar se ela responde e se o retorno mudou (formato dos dados ou código de resposta), já que isso quebra a integração mesmo sem nada ter mudado do nosso lado.

## Banco de dados

Verifico primeiro se o sistema está conseguindo se conectar ao banco (limite de conexões, credencial expirada), depois se há alguma operação travando as demais (lock), e se alguma alteração recente na estrutura do banco pode ter impactado a leitura ou gravação dos dados de veículos.

## Alterações recentes e deploy

Verifico se houve deploy ou atualização próximo ao horário em que o problema começou. Se houve, reviso o diff do último commit para entender o que mudou e se alguma alteração pode estar impactando a integração. Se não houve nenhum deploy, isso já reduz a chance de ser código e reforça as hipóteses de API externa ou credencial.

## Variáveis de ambiente

Verificaria se algo no `.env` mudou ou expirou desde ontem, já que é uma causa comum de "parou de repente" sem ninguém ter mexido no código: uma chave de API ou token da integração que expirou ou foi rotacionado, a URL da API externa apontando para o lugar errado, ou credenciais de banco alteradas. Compararia as variáveis atuais com o que era esperado, prestando atenção especial a qualquer segredo com data de validade.

## Ambiente de homologação e produção

Checaria se o problema acontece nos dois ambientes ou só em produção. Se em homologação funciona e em produção não, a diferença provavelmente está em configuração ou variável de ambiente específica de produção, não no código, o que estreita bastante a investigação. Reproduzir o erro em homologação primeiro também permite testar a correção sem risco antes de subir para produção.

## Testes

Rodaria a suíte de testes automatizados para ver se algo quebrou, e verificaria se existe cobertura para a integração de veículos especificamente. Se os testes passam mas o problema persiste em produção, isso é um sinal de que a falha está em algo que os testes não alcançam (ambiente externo, dados reais, configuração), e não na lógica da aplicação. Se não houver teste cobrindo esse fluxo, essa seria uma melhoria a fazer depois de resolver o incidente, para pegar uma regressão parecida mais cedo da próxima vez.

## Abordagem geral

De forma geral, minha ideia é não sair testando tudo de uma vez, e sim seguir a evidência: começo pelos logs, que apontam a direção, e a partir daí aprofundo no ponto mais provável. API externa, banco ou uma alteração recente. Isso ajuda a chegar na causa sem perder tempo verificando o que o erro já descartou.
