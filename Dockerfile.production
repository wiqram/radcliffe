FROM python:3.13-alpine

WORKDIR /app

COPY . .
RUN cp Homepage.html index.html

EXPOSE 3007

CMD ["python", "-m", "http.server", "3007", "--bind", "0.0.0.0"]
