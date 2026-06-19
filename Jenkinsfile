pipeline {

    agent none

    stages {
        /*stage('Delete App') {
            agent {
                kubernetes {
                    cloud 'kubernetes'
                    label 'kubeagent'
                    defaultContainer 'jnlp'
                }
            }
            steps {
                script {
                    sh 'kubectl delete --ignore-not-found=true -f compiled.yaml'
                    sleep(time:10,unit:"SECONDS")
                }
            }
        }*/
        /* No proto related activities performed in the pipeline. All proto generation should be handled in dev environment*/

        stage('Build App Images') {
            agent {
                kubernetes {
                    cloud 'kubernetes'
                    label 'kubeagent'
                    defaultContainer 'docker-agent'
                }
            }
            steps {
                script {
                    sh 'docker compose -f docker-compose-prod.yml build'
                }
            }
        }
        stage('Push App Images Repo') {
            agent {
                kubernetes {
                    cloud 'kubernetes'
                    label 'kubeagent'
                    defaultContainer 'docker-agent'
                }
            }
            steps {
                script {
                    sh 'docker compose -f docker-compose-prod.yml push radcliffe-web'
                }
            }
        }
        //instead of doing a fully deploy of namespace and all the pods, lets do rollout restart
        stage('Deploy K8s Yolo') {
            agent {
                kubernetes {
                    cloud 'kubernetes'
                    label 'kubeagent'
                    defaultContainer 'jnlp'
                }
            }
            steps {
                // Clean before build
                cleanWs()
                // We need to explicitly checkout from SCM here
                checkout scm
                echo "Building ${env.JOB_NAME}..."
                script {
//                    sh 'kubectl create -f yolo-namespace.yaml --dry-run=client -o yaml | kubectl apply -f -'
//                    sh 'kubectl apply -f compiled.yaml'
                    //sh 'kubectl rollout restart -f compiled-deployments.yaml'
//                    sh 'kubectl apply -f compiled-deployments.yaml'
//                    sh 'kubectl apply -f compiled-services.yaml'
//                    sh 'kubectl apply -f publisher-deleter-cronjob.yaml'
                    def error_exists = sh(
                            script: "kubectl get ns radcliffe", returnStatus: true)
                    //echo "namespace Yolo Doesnt exist - Error is non zero ==> - $error_exists"
                    if (error_exists == 0) {
                        echo "namespace is present doing rolling restart of deployment"
                        sh 'kubectl create -f namespace.yaml --dry-run=client -o yaml | kubectl apply -f -'
                        sh 'kubectl apply -f deployment.yaml'
                        sh 'kubectl rollout restart deployment -n radcliffe radcliffe-web'
                        //sh 'kubectl apply -f webui-deployment.yaml'
                        //sh 'kubectl rollout restart deployment -n ollama userinterface'
                        // Apply configmap first
                        //sh 'kubectl apply -f loader-model-configmap.yaml'
                        //sh 'kubectl apply -f loader-scripts-configmap.yaml'
                        // Deploy loader job
                        //sh 'kubectl apply -f ./optionsOllama/optionsstrategy-model-loader-job.yaml'
                        //sh 'kubectl apply -f loader-scripts-quant-model-loader-job.yaml'
                        return
                    } else {
                        echo "namespace radcliffe is not present"
                        sh 'kubectl create -f namespace.yaml --dry-run=client -o yaml | kubectl apply -f -'
                        sh 'kubectl apply -f deployment.yaml'
                        //sh 'kubectl apply -f webui-deployment.yaml'
                        // Apply configmap first
                        //sh 'kubectl apply -f loader-model-configmap.yaml'
                        //sh 'kubectl apply -f loader-scripts-configmap.yaml'
                        // Deploy loader job to deploy quant model based on llama 3.1
                        //sh 'kubectl apply -f loader-scripts-quant-model-loader-job.yaml'
                        return
                    }
                }
            }
        }
    }
}
