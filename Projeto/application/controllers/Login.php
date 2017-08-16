<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library(array('form_validation', 'LogArquivo'));
        $this->load->model(array('Login_model','Turma_model','Session_model')); //carregando o model
        $this->obj_Login_Model = new Login_model();
        $this->obj_sessao_model = new Session_model();
        date_default_timezone_set('America/Sao_Paulo');
        $this->obj_log = new LogArquivo();
    }

    public function index()
    {
        $dados['title'] = 'Login';
        $this->load->view('login', $dados);
        $this->load->view('uteis/cabecalho');
        $this->load->view('uteis/rodape');
    }

    public function login() {
        $dados['title'] = 'Login';
        $this->load->view('login', $dados);
        $this->load->view('uteis/cabecalho');
        $this->load->view('uteis/rodape');
    }

    public function menu(){
        $dados['title'] = 'Menu';
        $dados['email'] =  $this->session->userdata('usuario');
        $dados['empresa'] =  $this->session->userdata('empresa_nome');
        $this->load->view('login/menu_view', $dados);
    }

    public function CadastroUsuario(){
        $dados['title'] = 'Cadastro de Usuário';
        $this->load->view('login/Cadastrarusuario_view', $dados);
    }

    public function logar()
    {
        //Regras de validação
        $this->form_validation->set_rules('usuario', 'E-mail', 'trim|required');
        $this->form_validation->set_rules('senha', 'Senha', 'required');

        if ($this->form_validation->run() == FALSE) {
            //formulário com erros de preenchimento
            if (validation_errors())
                set_mensagem_sessao(validation_errors());
            else
                set_mensagem_sessao("Os dados estão incompletos");
            
            $this->login();

        } else {
            //verifica no banco se usuário e senha estao corretos
            $dados_form = $this->input->post();
            $retorno = $this->obj_Login_Model->logarSistema($dados_form['usuario'], $dados_form['senha']);

            if (isset($retorno) AND $retorno == 0) { //se foi logado

                $empresaid = 0;
                $empresanome = "";
                //se o retorno for numerico os dados informados não existem ou estao incorretos
                if ($retorno == 0) {
                    set_mensagem_sessao('O e-mail inserido não corresponde a nenhum cadastro. <br/><a href="'.base_url('Login/CadastroUsuario').'">Clique aqui e cadastre-se!</a>');
                } else {
                    set_mensagem_sessao('A senha inserida está incorreta. <br/><a href="#">Esqueceu a senha?</a>');
                }

                $user_dados = array(
                    'usuario' => $retorno['email'],
                    'senha' => $retorno['senha'],
                    'empresa_id' => $retorno['empresaid'],
                    'empresa_nome' => $retorno['razaosocial']
                );


                $this->session->set_userdata($user_dados);
                $this->login();

            } else {

                $this->login();
            }
        }
    }

    public function Cadastrar()
    {
        $this->load->view('uteis/cabecalho');

        $this->load->view('login/Cadastraraluno_view');

        $this->load->view('uteis/rodape');
    }

    function ValidaCadastro()
    {
        $this->load->model('Login_model');
        //Adicionando a variaveis o que veio do form
        $usuario = $this->input->post('usuario');
        $senha = $this->input->post('senha');
        $razaosocial = $this->input->post('razaosocial');

        $dadosUsuario = array(
            'usuario' => $usuario,
            'senha' => $senha,
            'razaosocial' => $razaosocial
        );

        $this->form_validation->set_rules('usuario', 'Username', 'required');
        $this->form_validation->set_rules('senha', 'Password', 'required');

        //Cadastrando usuario na empresa
        $cadastrado = $this->Login_model->CadastrarUsuario($dadosUsuario);

        if ($this->form_validation->run() == FALSE) {
            //formulário com erros de preenchimento
            if (validation_errors()) {
                set_mensagem_sessao(validation_errors());
            }else{
                set_mensagem_sessao("Os dados estão incompletos");
            }

            $this->CadastrarUsuario();
        } else {

            if ($cadastrado) {
                //gravando cadastro de usuario no log
                $this->index();

            } else {
                redirect('login/Cadastrar');
            }
        }
    }


    public function logout()
    {
        //session_destroy();
        //session_unset();
        array();
        redirect('Login');
    }

}


