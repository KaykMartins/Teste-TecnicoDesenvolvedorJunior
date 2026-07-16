# Bônus — Tela de cadastro de clientes

Tela de front-end (sem backend, sem persistência real) gerada em uma ferramenta separada (Claude Fable 5, com skill de desenvolvedor front-end), fora do escopo do Laravel — só o front-end estático, como pedido no enunciado.

## Código-fonte

[bonus/CadastroCliente.html](bonus/CadastroCliente.html) — arquivo único, pronto para abrir direto no navegador (React + Babel Standalone + Tailwind via CDN, sem build step). É o resultado final dos dois prompts abaixo.

## Prints

![Tela de cadastro de clientes — parte 1 (Identificação e Contato)](bonus/print-cadastro-clientes-1.png)

![Tela de cadastro de clientes — parte 2 (Endereço e resumo lateral)](bonus/print-cadastro-clientes-2.png)

## Prompts utilizados

### 1. Criação do projeto (site)

Configuração de execução: Modelo Claude Fable 5, skill "desenvolvedor front-end" ativa (arquivo anexado).

```
Desenvolva APENAS o front-end de uma tela de cadastro de clientes.
Não implemente backend, banco de dados nem persistência real — os dados
não precisam ser salvos, mas toda a validação deve funcionar no navegador.

Prioridade visual: a tela deve ser esteticamente polida e moderna, com
hierarquia clara, espaçamento generoso, tipografia legível e uma paleta
coerente. Evite aparência de template genérico.

Stack: React + Tailwind CSS, componente único, sem dependências pesadas.

Campos (marque visualmente os obrigatórios com asterisco e/ou cor):
- Nome Completo (obrigatório)
- CPF (obrigatório, com máscara 000.000.000-00)
- Telefone (obrigatório, com máscara (00) 00000-0000)
- E-mail (obrigatório)
- CEP (com máscara 00000-000)
- Logradouro
- Número
- Complemento
- Bairro
- Cidade
- Estado (dropdown com as 27 UFs)

Comportamento obrigatório:
- Validação client-side ao clicar em Salvar: nome não vazio, CPF com
  formato válido, telefone com formato válido, e-mail com formato válido.
- Mensagens de erro inline, abaixo de cada campo, em vermelho, aparecendo
  apenas após tentativa de envio ou ao sair do campo (blur).
- Botão "Salvar": valida tudo; se ok, exibe mensagem de sucesso; se não,
  destaca os campos com erro e rola até o primeiro.
- Botão "Cancelar": limpa todos os campos e remove mensagens de erro.

Layout:
- Organização em grid: campos de endereço agrupados visualmente
  (ex.: CEP/Logradouro/Número numa linha lógica).
- Responsivo: uma coluna no mobile, duas ou mais no desktop.
- Botões alinhados ao final do formulário, Salvar em destaque.

Entregue o código completo em um único arquivo, pronto para rodar.
```

### 2. Prompt de melhoria do front-end (redesign visual)

```
Você deve atuar como um Senior Front-end Engineer especializado em UI/UX Design, com experiência em criação de sistemas SaaS modernos, dashboards e aplicações corporativas.

Tenho um projeto front-end atualmente funcional, porém a interface está visualmente ruim: desalinhada, com excesso de espaços vazios, pouca hierarquia visual, aparência genérica e pouca preocupação com experiência do usuário.

O objetivo é fazer um redesign completo da camada visual, mantendo a funcionalidade existente.

Use como referência visual e de experiência:

https://doc.onvox.com.br/

Analise esse site como inspiração para:

organização das informações;
hierarquia visual;
espaçamento;
agrupamento de conteúdos;
experiência de navegação;
aparência profissional de software SaaS;
clareza dos formulários;
consistência visual.

Não copie o layout, utilize apenas como referência de qualidade e padrão visual.

Seu papel

Antes de alterar qualquer código:

Analise a interface atual.
Identifique problemas de:
espaçamento;
alinhamento;
proporção dos elementos;
excesso de áreas vazias;
falta de hierarquia;
problemas de UX;
componentes mal organizados.
Proponha uma estratégia de melhoria.
Depois implemente o redesign.
Prioridade principal

A prioridade é:

1. UX/UI Design
2. Organização visual
3. Experiência do usuário
4. Código limpo

Não quero apenas "deixar bonito". Quero uma interface com aparência de produto profissional.

Diretrizes de Design

Transforme a aplicação em algo próximo de um sistema SaaS moderno.

Características esperadas:

Layout limpo e profissional;
Melhor aproveitamento do espaço;
Menos áreas vazias sem propósito;
Conteúdo agrupado em blocos visuais;
Melhor equilíbrio entre textos, inputs e ações;
Hierarquia clara entre títulos, subtítulos e informações;
Componentes visualmente consistentes;
Boa leitura em telas grandes;
Excelente experiência no mobile.
Formulários

Os formulários atualmente parecem simples demais e pouco organizados.

Melhore:

agrupamento dos campos;
ordem das informações;
espaçamento entre seções;
labels;
placeholders;
mensagens de validação;
estados de erro;
estados de foco;
estados preenchidos.

Utilize conceitos de:

formulário em etapas quando fizer sentido;
cards/seções;
divisores visuais;
campos relacionados próximos.

Evite uma grande lista vertical de inputs sem organização.

Campos e componentes

Crie componentes visuais modernos:

Inputs:

altura consistente;
bordas suaves;
foco destacado;
transições;
ícones quando fizer sentido;
melhor contraste;
feedback visual.

Botões:

ação principal destacada;
ação secundária discreta;
estados hover;
estados loading se necessário.

Cards:

sombras leves;
bordas sutis;
espaçamento interno adequado.
Paleta visual

Crie uma identidade visual coerente.

Evitar:

cores muito fortes sem propósito;
excesso de roxo/neon;
aparência de template automático.

Priorizar:

fundo elegante;
contraste adequado;
cores profissionais;
destaque visual apenas onde necessário.
Responsividade

O design deve funcionar perfeitamente em:

desktop;
notebook;
tablet;
celular.

Não pode existir:

scroll horizontal;
elementos quebrados;
campos espremidos.
Experiência do usuário (UX)

Adicionar melhorias como:

feedback claro das ações;
mensagens de sucesso e erro melhores;
navegação intuitiva;
redução da carga visual;
informações importantes mais evidentes.

O usuário deve entender rapidamente:

onde está;
o que precisa preencher;
qual ação executar.
Regras técnicas
Não alterar regras de negócio.
Não criar backend.
Não modificar integrações existentes.
Não remover funcionalidades.
Manter compatibilidade com a arquitetura atual.
Priorizar componentes reutilizáveis.
Evitar código duplicado.
Resultado esperado

Ao finalizar, quero uma aplicação com aparência de:

produto SaaS premium;
sistema corporativo moderno;
interface desenvolvida por um time profissional de produto.

A sensação final deve ser:

"Esse sistema parece uma ferramenta comercial pronta para clientes."

Não entregue apenas sugestões. Faça as alterações necessárias no front-end.

Antes de codificar, apresente uma breve análise dos problemas encontrados e o plano de redesign.
```

## O que eu melhoraria antes de subir este projeto

1. A validação de CPF está incompleta. Hoje o regex só confere o formato `000.000.000-00`, mas não valida os dígitos verificadores. Na prática, `111.111.111-11` ou `123.456.789-00` passam sem problema, porque têm a pontuação certa. Num sistema onde o CPF é a chave de identidade do cliente, isso significa acumular registros com CPF sintaticamente correto e semanticamente inválido. Como o cálculo do dígito verificador é curto e determinístico, essa é a primeira correção que eu faria. O sistema não cumpre o que promete sem ela.

2. O projeto ainda é só a camada de apresentação, sem backend, banco ou segurança. O próprio rodapé deixa claro que nada é enviado a um servidor, e o "salvar" é apenas uma simulação com timeout; nada é persistido. Por isso, tudo que diz respeito a armazenamento e proteção (banco de dados, LGPD, criptografia do CPF em repouso, HTTPS, prevenção de SQL injection, validação no servidor e rate limiting) está integralmente em aberto, porque nenhuma dessas coisas pode existir dentro de um HTML de frontend puro. Essa parte pertence à camada que ainda não foi escrita.

Um ponto que quero deixar explícito, porque é fácil confundir: toda a validação que existe hoje neste arquivo (CPF, e-mail, telefone, o contador de campos obrigatórios) é conveniência de experiência do usuário, não segurança. Ela roda no navegador, que o usuário controla e consegue contornar pelo DevTools em segundos. Quando existir um backend, essa validação precisa ser reescrita no servidor. Não é para descartar a do frontend, que melhora a experiência, mas ela ajuda o usuário, não protege o sistema. O servidor valida porque não confia em ninguém, nem no próprio frontend.

### Roadmap, em ordem, para levar isto a sério

Primeiro, corrigir a validação de CPF aqui mesmo, com o dígito verificador. É barato e essencial. Depois, definir a stack de backend e subir uma API com um endpoint de criação real, repetindo toda a validação no servidor e usando queries parametrizadas ou ORM para eliminar SQL injection. Em seguida, conectar o banco (Postgres resolve), tratando o CPF como dado sensível: HTTPS obrigatório, criptografia em repouso e uma decisão consciente sobre unicidade, já que hoje cadastrar o mesmo CPF duas vezes não gera nenhuma consequência. Em paralelo, a camada de LGPD: base legal para a coleta, política de retenção e um mecanismo de exclusão a pedido do titular. Por último, rate limiting no endpoint e registro de logs de acesso.
