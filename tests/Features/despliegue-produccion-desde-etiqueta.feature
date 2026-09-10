# language: es
Característica: Despliegue de producción solo desde etiqueta
  Como revista académica
  quiero que el FTPS a producción se dispare solo desde una etiqueta vX.Y.Z
  para que un Run workflow desde main no suba código aunque HEAD esté etiquetado
  # ADR 0020. Ejecuta: tools/qa-require-production-release-tag.sh

  Escenario: Un workflow_dispatch desde main se rechaza aunque HEAD esté etiquetado
    Dado un commit alcanzable desde main
    Y una etiqueta anotada vX.Y.Z que coincide con package.json
    Cuando se dispara el workflow con ref de rama main
    Entonces el gate aborta
    Y no hay FTPS

  Escenario: Un workflow_dispatch desde la etiqueta vX.Y.Z puede continuar
    Dado un commit alcanzable desde main
    Y una etiqueta anotada vX.Y.Z que coincide con package.json
    Cuando se dispara el workflow con ref de esa etiqueta
    Entonces el gate acepta el release
