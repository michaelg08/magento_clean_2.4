FROM docker/whalesay:latest
LABEL Name=magentoclean Version=0.0.1
RUN apt-get -y update && apt-get install -y fortunes
RUN apt-get git
    
CMD ["sh", "-c", "/usr/games/fortune -a | cowsay"]
EXPOSE 80:7777