FROM python:3 AS build

WORKDIR /usr/src/app

RUN pip install --no-cache-dir --upgrade pip && \
    pip install --no-cache-dir mkdocs-material && \
    pip install --no-cache-dir mkdocs-glightbox

COPY . .

RUN mkdocs build


FROM nginx AS publish

COPY --from=build /usr/src/app/site/ /usr/share/nginx/html/
